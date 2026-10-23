<?php

namespace App\Http\Controllers;

use App\Models\Entete;
use App\Models\Facture;
use App\Models\McfRapport;
use App\Services\Mcf\McfSerialDriver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    protected McfSerialDriver $driver;

    public function __construct(McfSerialDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Calcule le timbre de quittance officiel DGI selon le barème progressif (espèces).
     */
    public static function calculerTimbreFiscal(float $montant): float
    {
        if ($montant >= 1000 && $montant <= 5000) return 30.0;
        if ($montant >= 5001 && $montant <= 10000) return 50.0;
        if ($montant >= 10001 && $montant <= 50000) return 100.0;
        if ($montant > 50000) {
            return 100.0 + (ceil(($montant - 50000) / 50000) * 50.0);
        }
        return 0.0;
    }

    /**
     * Moteur d'agrégation fiscale et financière DGI / SECeF.
     */
    public function agregerDonneesPeriode(?string $dateDebut = null, ?string $dateFin = null): array
    {
        $debut = $dateDebut ? Carbon::parse($dateDebut)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $fin   = $dateFin ? Carbon::parse($dateFin)->endOfDay() : Carbon::now()->endOfDay();

        $factures = Facture::with([
            'Contribuable.Categorie',
            'Typefacture',
            'Modepaie',
            'Bic',
            'Reglements',
            'McfCertifications',
            'ElementFactures.Produit.tva',
            'ElementFactures.Produit.bic',
        ])
        ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
        ->orderBy('date', 'desc')
        ->orderBy('id', 'desc')
        ->get();

        $totalHtGlobal     = 0.0;
        $totalTvaGlobal    = 0.0;
        $totalTsGlobal     = 0.0;
        $totalPsvbGlobal   = 0.0;
        $totalTimbreGlobal = 0.0;
        $totalTtcGlobal    = 0.0;
        $totalReductions   = 0.0;
        $totalAvoirs       = 0.0;

        $nbVentes = 0;
        $nbAvoirs = 0;

        $groupesTaxes = [
            'A' => ['libelle' => 'Groupe A (Exonéré 0%)', 'taux' => 0.0, 'base_ht' => 0.0, 'tva' => 0.0, 'ts' => 0.0],
            'B' => ['libelle' => 'Groupe B (Normal 18%)', 'taux' => 18.0, 'base_ht' => 0.0, 'tva' => 0.0, 'ts' => 0.0],
            'C' => ['libelle' => 'Groupe C (Exportation)', 'taux' => 0.0, 'base_ht' => 0.0, 'tva' => 0.0, 'ts' => 0.0],
            'D' => ['libelle' => 'Groupe D (Taux réduit)', 'taux' => 0.0, 'base_ht' => 0.0, 'tva' => 0.0, 'ts' => 0.0],
        ];

        $modesPaiement = [
            'Espèces'      => 0.0,
            'Virement'     => 0.0,
            'Chèque'       => 0.0,
            'Mobile Money' => 0.0,
            'Autre'        => 0.0,
        ];

        $parTypeFacture = [];

        foreach ($factures as $f) {
            $typeCode = strtoupper(optional($f->Typefacture)->code ?? 'FV');
            $isAvoir  = in_array($typeCode, ['FA', 'EA'], true);
            $signe    = $isAvoir ? -1.0 : 1.0;

            if ($isAvoir) $nbAvoirs++;
            else $nbVentes++;

            if (!isset($parTypeFacture[$typeCode])) {
                $parTypeFacture[$typeCode] = [
                    'libelle'   => $isAvoir ? "Avoirs ($typeCode)" : "Ventes ($typeCode)",
                    'count'     => 0,
                    'total_ht'  => 0.0,
                    'total_tva' => 0.0,
                    'total_ttc' => 0.0,
                ];
            }
            $parTypeFacture[$typeCode]['count']++;

            $factureHt  = 0.0;
            $factureTva = 0.0;
            $factureTs  = 0.0;

            foreach ($f->ElementFactures as $el) {
                $codeProd = strtoupper((string) optional($el->Produit)->code);
                if ($codeProd === 'TRANS') continue;

                $qte      = (float) $el->quantite;
                $pu       = (float) $el->prix_unitaire;
                $ligneHt  = $qte * $pu;
                $tsUnit   = (float) (optional($el->Produit)->montant_ts ?? 0.0);
                $ligneTs  = $tsUnit * $qte;

                $tauxTva = (float) (optional(optional($el->Produit)->tva)->taux ?? 0.0);
                $baseTva = $ligneHt + $ligneTs;
                $ligneTva = $tauxTva > 0 ? round($baseTva * ($tauxTva / 100.0), 2) : 0.0;

                $grpKey = $tauxTva > 0 ? 'B' : 'A';
                if (!isset($groupesTaxes[$grpKey])) {
                    $groupesTaxes[$grpKey] = [
                        'libelle' => "Groupe {$grpKey} ({$tauxTva}%)",
                        'taux'    => $tauxTva,
                        'base_ht' => 0.0,
                        'tva'     => 0.0,
                        'ts'      => 0.0,
                    ];
                }

                $groupesTaxes[$grpKey]['base_ht'] += ($ligneHt * $signe);
                $groupesTaxes[$grpKey]['tva']     += ($ligneTva * $signe);
                $groupesTaxes[$grpKey]['ts']      += ($ligneTs * $signe);

                $factureHt  += $ligneHt;
                $factureTva += $ligneTva;
                $factureTs  += $ligneTs;
            }

            $remise = (float) (($f->remise_1 ?? 0) + ($f->remise_2 ?? 0));
            $netCommercial = max(0, $factureHt - $remise);

            $tauxPsvb = (float) (optional($f->Bic)->taux ?? 0.0);
            $facturePsvb = $tauxPsvb > 0 ? round(($netCommercial + $factureTva) * ($tauxPsvb / 100.0), 2) : 0.0;

            $factureTtc = $netCommercial + $factureTva + $factureTs + $facturePsvb;

            // Détection du mode de paiement
            $modeLibelle = 'Espèces';
            $modeCode    = 'E';
            if ($f->Modepaie) {
                $code = strtoupper((string) $f->Modepaie->code);
                $lib  = $f->Modepaie->libelle;
                if (str_contains($code, 'ESP') || str_contains($code, 'E')) {
                    $modeLibelle = 'Espèces'; $modeCode = 'E';
                } elseif (str_contains($code, 'VIR') || str_contains($code, 'V')) {
                    $modeLibelle = 'Virement'; $modeCode = 'V';
                } elseif (str_contains($code, 'CHQ') || str_contains($code, 'C')) {
                    $modeLibelle = 'Chèque'; $modeCode = 'C';
                } elseif (str_contains($code, 'MOB') || str_contains($code, 'M') || str_contains($code, 'OM') || str_contains($code, 'MOOV')) {
                    $modeLibelle = 'Mobile Money'; $modeCode = 'M';
                } else {
                    $modeLibelle = $lib ?: 'Autre';
                }
            }

            // Calcul du timbre de quittance en cas de paiement espèces
            $factureTimbre = ($modeCode === 'E') ? self::calculerTimbreFiscal($factureTtc) : 0.0;

            // Enregistrement des règlements réels s'il y en a
            if ($f->Reglements->count() > 0) {
                foreach ($f->Reglements as $reg) {
                    $regMode = strtoupper((string) ($reg->mode ?? 'ESPECES'));
                    if (str_contains($regMode, 'VIR') || str_contains($regMode, 'V')) $nomMode = 'Virement';
                    elseif (str_contains($regMode, 'CHQ') || str_contains($regMode, 'C')) $nomMode = 'Chèque';
                    elseif (str_contains($regMode, 'MOB') || str_contains($regMode, 'M')) $nomMode = 'Mobile Money';
                    else $nomMode = 'Espèces';

                    $modesPaiement[$nomMode] = ($modesPaiement[$nomMode] ?? 0.0) + ((float) $reg->montant * $signe);
                }
            } else {
                $modesPaiement[$modeLibelle] = ($modesPaiement[$modeLibelle] ?? 0.0) + ($factureTtc * $signe);
            }

            // Cumuls globaux
            $totalHtGlobal     += ($netCommercial * $signe);
            $totalTvaGlobal    += ($factureTva * $signe);
            $totalTsGlobal     += ($factureTs * $signe);
            $totalPsvbGlobal   += ($facturePsvb * $signe);
            $totalTimbreGlobal += ($factureTimbre * $signe);
            $totalTtcGlobal    += ($factureTtc * $signe);

            if ($isAvoir) {
                $totalAvoirs += $factureTtc;
            } else {
                $totalReductions += $remise;
            }

            $parTypeFacture[$typeCode]['total_ht']  += $netCommercial;
            $parTypeFacture[$typeCode]['total_tva'] += $factureTva;
            $parTypeFacture[$typeCode]['total_ttc'] += $factureTtc;
        }

        // Ventes incomplètes (brouillons ou erreurs sur la même période)
        $ventesIncompletes = Facture::whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
            ->where(function ($q) {
                $q->whereNull('dgi_signature')->orWhere('statut', 'BROUILLON');
            })
            ->count();

        return [
            'periode' => [
                'debut' => $debut->toDateString(),
                'fin'   => $fin->toDateString(),
            ],
            'factures'     => $factures,
            'statistiques' => [
                'chiffre_affaires_ht'    => round($totalHtGlobal, 2),
                'tva_collectee'          => round($totalTvaGlobal, 2),
                'taxes_specifiques'      => round($totalTsGlobal, 2),
                'psvb_collecte'          => round($totalPsvbGlobal, 2),
                'timbre_quittance'       => round($totalTimbreGlobal, 2),
                'total_ttc'              => round($totalTtcGlobal, 2),
                'reductions_commerciales'=> round($totalReductions, 2),
                'total_avoirs'           => round($totalAvoirs, 2),
                'nb_ventes'              => $nbVentes,
                'nb_avoirs'              => $nbAvoirs,
                'ventes_incompletes'     => $ventesIncompletes,
                'groupes_taxes'          => $groupesTaxes,
                'modes_paiement'         => $modesPaiement,
                'par_type_facture'       => $parTypeFacture,
            ],
        ];
    }

    /**
     * Écran principal : Bilan périodique, KPIs, ventilation fiscale et gestion des rapports X/Z.
     */
    public function index(Request $request)
    {
        $dateDebut = $request->input('date_debut', Carbon::now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', Carbon::now()->toDateString());

        $donnees = $this->agregerDonneesPeriode($dateDebut, $dateFin);

        // Stats du jour pour les actions rapides Rapport X / Clôture Z
        $donneesJour = $this->agregerDonneesPeriode(Carbon::today()->toDateString(), Carbon::today()->toDateString());

        // Historique des rapports fiscaux enregistrés
        $historiqueRapports = McfRapport::with('user')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $dernierZ = McfRapport::where('type_rapport', 'Z')->orderBy('id', 'desc')->first();
        $dernierX = McfRapport::where('type_rapport', 'X')->orderBy('id', 'desc')->first();

        $entete = Entete::singleton();

        return view('pages.rapports.index', compact(
            'donnees',
            'donneesJour',
            'historiqueRapports',
            'dernierZ',
            'dernierX',
            'dateDebut',
            'dateFin',
            'entete'
        ));
    }

    /**
     * Génère un Rapport X (Lecture journalière non destructive).
     */
    public function genererRapportX(Request $request)
    {
        try {
            $donnees = $this->agregerDonneesPeriode(Carbon::today()->toDateString(), Carbon::today()->toDateString());
            $stats   = $donnees['statistiques'];

            // Appel au boîtier MCF Eltrade (ou simulateur natif)
            $mcfRes = $this->driver->genererRapport('X');

            $rapport = McfRapport::create([
                'type_rapport'            => 'X',
                'numero_rapport'          => McfRapport::prochainNumero('X'),
                'date_rapport'            => Carbon::now(),
                'date_debut'              => Carbon::today()->startOfDay(),
                'date_fin'                => Carbon::now(),
                'chiffre_affaires_ht'     => $stats['chiffre_affaires_ht'],
                'total_tva'               => $stats['tva_collectee'],
                'total_ts'                => $stats['taxes_specifiques'],
                'total_psvb'              => $stats['psvb_collecte'],
                'total_timbre'            => $stats['timbre_quittance'],
                'total_ttc'               => $stats['total_ttc'],
                'reductions_commerciales' => $stats['reductions_commerciales'],
                'nb_factures_vente'       => $stats['nb_ventes'],
                'nb_factures_avoir'       => $stats['nb_avoirs'],
                'ventes_incompletes'      => $stats['ventes_incompletes'],
                'details_taxes'           => $stats['groupes_taxes'],
                'details_reglements'      => $stats['modes_paiement'],
                'details_bruts'           => $stats,
                'statut'                  => 'ENREGISTRE',
                'raw_mcf_response'        => json_encode($mcfRes),
                'user_id'                 => Auth::id(),
            ]);

            smilify('success', "Rapport X N° {$rapport->numero_rapport} généré avec succès.");
            return redirect()->route('rapports.index');
        } catch (\Throwable $e) {
            report($e);
            smilify('error', "Échec de génération du Rapport X : " . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Génère une Clôture journalière fiscale Z (Archive et réinitialise les compteurs journaliers).
     */
    public function genererRapportZ(Request $request)
    {
        try {
            $dernierZ = McfRapport::where('type_rapport', 'Z')->orderBy('id', 'desc')->first();
            $debutPeriode = $dernierZ ? $dernierZ->date_rapport : Carbon::today()->startOfDay();

            $donnees = $this->agregerDonneesPeriode($debutPeriode->toDateString(), Carbon::today()->toDateString());
            $stats   = $donnees['statistiques'];

            // Appel au boîtier MCF Eltrade (commande 45h Z)
            $mcfRes = $this->driver->genererRapport('Z');

            $rapport = McfRapport::create([
                'type_rapport'            => 'Z',
                'numero_rapport'          => McfRapport::prochainNumero('Z'),
                'date_rapport'            => Carbon::now(),
                'date_debut'              => $debutPeriode,
                'date_fin'                => Carbon::now(),
                'chiffre_affaires_ht'     => $stats['chiffre_affaires_ht'],
                'total_tva'               => $stats['tva_collectee'],
                'total_ts'                => $stats['taxes_specifiques'],
                'total_psvb'              => $stats['psvb_collecte'],
                'total_timbre'            => $stats['timbre_quittance'],
                'total_ttc'               => $stats['total_ttc'],
                'reductions_commerciales' => $stats['reductions_commerciales'],
                'nb_factures_vente'       => $stats['nb_ventes'],
                'nb_factures_avoir'       => $stats['nb_avoirs'],
                'ventes_incompletes'      => $stats['ventes_incompletes'],
                'details_taxes'           => $stats['groupes_taxes'],
                'details_reglements'      => $stats['modes_paiement'],
                'details_bruts'           => $stats,
                'statut'                  => 'CLOTURE',
                'raw_mcf_response'        => json_encode($mcfRes),
                'user_id'                 => Auth::id(),
            ]);

            smilify('success', "Clôture fiscale Z N° {$rapport->numero_rapport} exécutée et archivée.");
            return redirect()->route('rapports.imprimer', $rapport->id);
        } catch (\Throwable $e) {
            report($e);
            smilify('error', "Échec de la clôture fiscale Z : " . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Impression officielle d'un rapport X ou Z (Format Ticket et A4).
     */
    public function imprimer($id)
    {
        $rapport = McfRapport::with('user')->findOrFail($id);
        $entete  = Entete::singleton();

        return view('pages.rapports.print_rapport', compact('rapport', 'entete'));
    }

    /**
     * Impression officielle du Bilan périodique pour la DGI / Comptabilité.
     */
    public function printBilan(Request $request)
    {
        $dateDebut = $request->input('date_debut', Carbon::now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', Carbon::now()->toDateString());

        $donnees = $this->agregerDonneesPeriode($dateDebut, $dateFin);
        $entete  = Entete::singleton();

        return view('pages.rapports.print_bilan', compact('donnees', 'entete', 'dateDebut', 'dateFin'));
    }

    /**
     * Génère un Rapport A (Articles / Bilan des ventes par article et état des stocks).
     */
    public function genererRapportA(Request $request)
    {
        try {
            $dernierA = McfRapport::where('type_rapport', 'A')->orderBy('id', 'desc')->first();
            $debutPeriode = $dernierA ? $dernierA->date_rapport : Carbon::now()->startOfMonth()->startOfDay();
            $finPeriode   = Carbon::now();

            $articlesData = $this->calculerBilanArticles($debutPeriode->toDateString(), $finPeriode->toDateString());

            // Commande MCF ou émulation
            $mcfRes = $this->driver->genererRapport('A');

            $totalVentes = array_sum(array_column($articlesData, 'montant_vendu'));
            $totalRetours = array_sum(array_column($articlesData, 'montant_retourne'));

            $rapport = McfRapport::create([
                'type_rapport'            => 'A',
                'numero_rapport'          => McfRapport::prochainNumero('A'),
                'date_rapport'            => Carbon::now(),
                'date_debut'              => $debutPeriode,
                'date_fin'                => $finPeriode,
                'chiffre_affaires_ht'     => $totalVentes - $totalRetours,
                'total_tva'               => 0.0,
                'total_ts'                => 0.0,
                'total_psvb'              => 0.0,
                'total_timbre'            => 0.0,
                'total_ttc'               => $totalVentes - $totalRetours,
                'reductions_commerciales' => 0.0,
                'nb_factures_vente'       => count($articlesData),
                'nb_factures_avoir'       => 0,
                'ventes_incompletes'      => 0,
                'details_taxes'           => [],
                'details_reglements'      => [],
                'details_bruts'           => ['articles' => $articlesData],
                'statut'                  => 'CLOTURE',
                'raw_mcf_response'        => json_encode($mcfRes),
                'user_id'                 => Auth::id(),
            ]);

            \App\Models\AuditLog::enregistrer('GENERATE', 'RAPPORT', "Génération du Rapport A (Articles) N° {$rapport->numero_rapport}");

            smilify('success', "Rapport Articles (A) N° {$rapport->numero_rapport} généré et archivé avec succès.");
            return redirect()->route('rapports.articles.imprimer', ['rapport_id' => $rapport->id]);
        } catch (\Throwable $e) {
            report($e);
            smilify('error', "Échec de génération du Rapport A : " . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Calcule le bilan des articles sur une période.
     */
    public function calculerBilanArticles(?string $dateDebut = null, ?string $dateFin = null): array
    {
        $debut = $dateDebut ? Carbon::parse($dateDebut)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $fin   = $dateFin ? Carbon::parse($dateFin)->endOfDay() : Carbon::now()->endOfDay();

        $factures = Facture::with([
            'Typefacture',
            'ElementFactures.Produit.tva',
        ])
        ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
        ->get();

        $articlesStats = [];

        foreach ($factures as $f) {
            $typeCode = strtoupper(optional($f->Typefacture)->code ?? 'FV');
            $isAvoir  = in_array($typeCode, ['FA', 'EA'], true);

            foreach ($f->ElementFactures as $el) {
                $prod = $el->Produit;
                $code = $prod ? $prod->code : ($el->code_produit ?? 'DIV');
                $nom  = $prod ? $prod->designation : ($el->designation ?? 'Article divers');
                $cle  = $code . '_' . $nom;

                if (!isset($articlesStats[$cle])) {
                    $tauxTva = (float) (optional(optional($prod)->tva)->taux ?? 0.0);
                    $articlesStats[$cle] = [
                        'code'               => $code,
                        'nom'                => $nom,
                        'prix_unitaire'      => (float) $el->prix_unitaire,
                        'taux_impot'         => $tauxTva,
                        'quantite_vendue'    => 0.0,
                        'montant_vendu'      => 0.0,
                        'quantite_retournee' => 0.0,
                        'montant_retourne'   => 0.0,
                        'quantite_stock'     => (float) (optional($prod)->qte ?? 0.0),
                    ];
                }

                $q = (float) $el->quantite;
                $pu = (float) $el->prix_unitaire;
                $ligneTotal = $q * $pu;

                if ($isAvoir) {
                    $articlesStats[$cle]['quantite_retournee'] += $q;
                    $articlesStats[$cle]['montant_retourne']   += $ligneTotal;
                } else {
                    $articlesStats[$cle]['quantite_vendue'] += $q;
                    $articlesStats[$cle]['montant_vendu']   += $ligneTotal;
                }
            }
        }

        // Compléter avec les articles existants en stock qui n'ont pas encore été vendus sur la période
        $tousProduits = \App\Models\Produit::with('tva')->get();
        foreach ($tousProduits as $p) {
            $cle = $p->code . '_' . $p->designation;
            if (!isset($articlesStats[$cle])) {
                $articlesStats[$cle] = [
                    'code'               => $p->code,
                    'nom'                => $p->designation,
                    'prix_unitaire'      => (float) $p->prix_unitaire,
                    'taux_impot'         => (float) (optional($p->tva)->taux ?? 0.0),
                    'quantite_vendue'    => 0.0,
                    'montant_vendu'      => 0.0,
                    'quantite_retournee' => 0.0,
                    'montant_retourne'   => 0.0,
                    'quantite_stock'     => (float) $p->qte,
                ];
            }
        }

        return array_values($articlesStats);
    }

    /**
     * Impression du Rapport A (Mouvements et Bilan des Articles).
     */
    public function printArticles(Request $request)
    {
        $rapportId = $request->input('rapport_id');
        $dateDebut = $request->input('date_debut', Carbon::now()->startOfMonth()->toDateString());
        $dateFin   = $request->input('date_fin', Carbon::now()->toDateString());

        if ($rapportId) {
            $rapport = McfRapport::findOrFail($rapportId);
            $articlesData = $rapport->details_bruts['articles'] ?? [];
            $dateDebut = $rapport->date_debut ? $rapport->date_debut->toDateString() : $dateDebut;
            $dateFin = $rapport->date_fin ? $rapport->date_fin->toDateString() : $dateFin;
        } else {
            $rapport = null;
            $articlesData = $this->calculerBilanArticles($dateDebut, $dateFin);
        }

        $entete = Entete::singleton();

        return view('pages.rapports.print_rapport_a', compact('articlesData', 'entete', 'dateDebut', 'dateFin', 'rapport'));
    }
}
