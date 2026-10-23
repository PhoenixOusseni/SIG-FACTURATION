<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFactureAvoirRequest;
use App\Models\FactureAvoir;
use App\Models\ElementFactureAvoir;
use App\Models\Facture;
use App\Models\Contribuable;
use App\Models\Entete;
use App\Models\Produit;
use App\Services\SFE\InvoiceSigningService;
use App\Services\SFE\SfeInvoiceType;
use App\Services\SFE\StatusSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FactureAvoirController extends Controller
{
    protected $sfeSigner;
    protected $statusSnapshots;

    public function __construct(InvoiceSigningService $sfeSigner, StatusSnapshotService $statusSnapshots)
    {
        $this->sfeSigner = $sfeSigner;
        $this->statusSnapshots = $statusSnapshots;
    }

    /**
     * Liste des factures d'avoir de l'utilisateur connecté.
     */
    public function index()
    {
        $collection = FactureAvoir::with(['Contribuable', 'Facture', 'User'])
            ->where('users_id', auth()->id())
            ->orderByDesc('id')
            ->get();

        $factures = Facture::with(['Contribuable', 'ElementFactures.Produit', 'FactureAvoirs.ElementFactureAvoirs.Produit.tva'])
            ->whereNotIn('statut', ['annulée', 'Annulée par Avoir'])
            ->orderByDesc('id')
            ->get()
            ->filter(function ($f) {
                return $f->isCertified() && $f->peutSubirAvoir();
            })
            ->values();

        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::with('tva')->orderBy('code')->get();
        $produitsData = $produits->keyBy('id')->map(function ($produit) {
            return [
                'code' => $produit->code,
                'designation' => $produit->designation,
                'prix_unitaire' => (float) $produit->prix_unitaire,
                'qte' => (float) $produit->qte,
                'taux_tva' => (float) optional(optional($produit)->tva)->taux,
                'type_ts' => (string) ($produit->type_ts ?? 'AUCUNE'),
                'montant_ts' => (float) ($produit->montant_ts ?? 0),
                'pourcentage_ts' => (float) ($produit->pourcentage_ts ?? 0),
                'is_bien' => (bool) $produit->isBien(),
                'is_service' => (bool) $produit->isService(),
            ];
        });

        // Préparer les données factures pour auto-remplissage et rappel dans le formulaire
        $facturesData = [];
        foreach ($factures as $f) {
            $tauxBic = (float) optional($f->Bic)->taux;
            $facturesData[$f->id] = [
                'code' => $f->code,
                'date' => $f->date ? \Carbon\Carbon::parse($f->date)->format('d/m/Y') : '',
                'client_id' => $f->Contribuable?->id,
                'client_name' => $f->Contribuable?->assujeti,
                'client_ifu' => $f->Contribuable?->ifu,
                'taux_bic' => $tauxBic,
                'total_facture' => (float) $f->montant_total,
                'solde_disponible' => (float) $f->soldeAvoirDisponible(),
                'a_subi_avoir' => (bool) $f->aSubiAvoir(),
                'a_deja_avoir_partiel' => (bool) $f->aDejaAvoirPartiel(),
                'can_ran' => (bool) (!$f->aSubiAvoir()),
                'montant_paye' => (float) $f->montant_paye,
                'reste_a_payer' => (float) $f->reste_a_payer,
                'trop_percu' => (float) $f->trop_percu,
                'statut_paiement' => $f->statut_paiement,
                'objet' => $f->objet,
                'reference' => $f->reference,
                'remise_1' => (float) ($f->remise_1 ?? 0),
                'remise_2' => (float) ($f->remise_2 ?? 0),
                'lignes' => $f->ElementFactures->map(function ($element) use ($f, $tauxBic) {
                    $prod = $element->Produit;
                    $qteInitiale = (float) $element->quantite;
                    $qteDejaAvoir = $f->quantiteAvoirProduit((int) $element->produits_id);
                    $qteRestante = max(0.0, round($qteInitiale - $qteDejaAvoir, 2));

                    $qte = $qteRestante;
                    $pu = (float) $element->prix_unitaire;
                    $montantHt = $qte * $pu;

                    $typeTs = $prod ? (string) $prod->type_ts : 'AUCUNE';
                    $montantTsFixe = $prod ? (float) ($prod->montant_ts ?? 0) : 0.0;
                    $pctTs = $prod ? (float) ($prod->pourcentage_ts ?? 0) : 0.0;
                    $tsUnit = 0.0;
                    if ($typeTs === 'POURCENTAGE' && $pctTs > 0) {
                        $tsUnit = round($pu * ($pctTs / 100.0), 2);
                    } elseif ($montantTsFixe > 0) {
                        $tsUnit = $montantTsFixe;
                    }
                    $tsTotal = round($tsUnit * $qte, 2);

                    $groupeTva = $prod ? strtoupper((string) optional($prod->tva)->code ?: 'A') : 'A';
                    $tauxTva = ($groupeTva === 'A') ? 0.0 : (float) optional(optional($prod)->tva)->taux;
                    $baseTva = $montantHt + $tsTotal;
                    $tvaTotal = round($baseTva * ($tauxTva / 100.0), 2);
                    $ttcTotal = $baseTva + $tvaTotal;

                    $isBien = $prod ? (bool) $prod->isBien() : false;
                    $bicTotal = ($isBien && $tauxBic > 0) ? round($ttcTotal * ($tauxBic / 100.0), 2) : 0.0;
                    $totalTtcLigne = $ttcTotal + $bicTotal;

                    return [
                        'produit_id' => $element->produits_id,
                        'designation' => $element->designation ?: optional($prod)->designation,
                        'quantite_initiale' => $qteInitiale,
                        'quantite_deja_avoir' => $qteDejaAvoir,
                        'quantite_restante' => $qteRestante,
                        'quantite' => $qteRestante,
                        'prix_unitaire' => $pu,
                        'montant_ht' => $montantHt,
                        'type_ts' => $typeTs,
                        'montant_ts' => $montantTsFixe,
                        'pourcentage_ts' => $pctTs,
                        'ts_unit' => $tsUnit,
                        'ts_total' => $tsTotal,
                        'groupe_tva' => $groupeTva,
                        'taux_tva' => $tauxTva,
                        'tva_total' => $tvaTotal,
                        'ttc_total' => $ttcTotal,
                        'is_service' => $prod ? (bool) $prod->isService() : false,
                        'is_bien' => $isBien,
                        'bic_total' => $bicTotal,
                        'total_ttc_ligne' => $totalTtcLigne,
                    ];
                })->values(),
            ];
        }
        $facturesDataJson = json_encode($facturesData);
        $sourceFactureId = (string) request('facture_id', '');
        $sfeState = $this->statusSnapshots->current();

        $categories = \App\Models\Categorie::orderBy('code')->get();
        $tvas = \App\Models\Tva::orderBy('code')->get();
        $bics = \App\Models\Bic::orderBy('code')->get();

        return view('pages.facture_avoir.index', compact(
            'collection',
            'factures',
            'contribuables',
            'produits',
            'produitsData',
            'facturesDataJson',
            'sourceFactureId',
            'sfeState',
            'categories',
            'tvas',
            'bics'
        ));
    }

    /**
     * Liste de toutes les factures d'avoir.
     */
    public function all_facture()
    {
        $collection = FactureAvoir::with(['Contribuable', 'Facture', 'User'])
            ->orderByDesc('id')
            ->get();

        return view('pages.facture_avoir.liste', compact('collection'));
    }

    /**
     * Enregistrer une nouvelle facture d'avoir + ses éléments.
     */
    public function store(StoreFactureAvoirRequest $request)
    {
        $nature = SfeInvoiceType::normalizeNature($request->nature_avoir)
            ?: SfeInvoiceType::natureFromMotif($request->motif)
            ?: 'RAN';

        $facture_avoir_result = DB::transaction(function () use ($request, $nature) {
            $factureOrigine = Facture::findOrFail($request->facture_id);
            if ($factureOrigine->aAvoirTotal()) {
                throw new \Exception("La facture {$factureOrigine->code} a déjà été intégralement annulée par un avoir précédent.");
            }

            // Générer automatiquement le code de la facture d'avoir (AVR-00001/2026)
            $year = date('Y');
            $lastAvoir = FactureAvoir::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = ($lastAvoir ? intval(substr($lastAvoir->code, 4, 5)) : 0) + 1;
            $generatedCode = 'AVR-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            // Commentaires DGI pour l'Avoir (Lignes A à H + Mentions légales)
            $notesData = [];
            foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $letter) {
                $field = 'comment_' . strtolower($letter);
                if ($request->filled($field)) {
                    $notesData[$letter] = substr(trim($request->input($field)), 0, 30);
                }
            }
            if ($request->filled('mention_legale')) {
                $notesData['mention'] = trim($request->input('mention_legale'));
            }
            if ($request->filled('notes_libres')) {
                $notesData['notes_libres'] = trim($request->input('notes_libres'));
            }
            $notesJson = !empty($notesData) ? json_encode($notesData, JSON_UNESCAPED_UNICODE) : null;

            $remise1 = $request->filled('remise_1') ? (float) $request->remise_1 : (float) ($factureOrigine->remise_1 ?? 0);
            $remise2 = $request->filled('remise_2') ? (float) $request->remise_2 : (float) ($factureOrigine->remise_2 ?? 0);

            $facture_avoir = FactureAvoir::create([
                'code'             => $generatedCode,
                'reference'        => $request->reference,
                'date'             => $request->date ?? now()->format('Y-m-d'),
                'statut'           => $request->statut ?? 'en attente',
                'users_id'         => auth()->id(),
                'contribuables_id' => $request->contribuables_id,
                'facture_id'       => $request->facture_id,
                'objet'            => $request->objet,
                'motif'            => $request->motif ?? $nature,
                'nature_avoir'     => $nature,
                'remise_1'         => $remise1,
                'remise_2'         => $remise2,
                'notes'            => $notesJson,
            ]);

            $this->saveElements($facture_avoir, $request, $nature);

            // Mettre à jour le statut de la facture d'origine selon la nature fiscale réelle de l'avoir
            if ($nature === 'RAN') {
                $factureOrigine->update(['statut' => 'Annulée par Avoir']);
            } elseif ($nature === 'RRR') {
                $factureOrigine->update(['statut' => 'Remise accordée']);
            } elseif ($nature === 'RAM') {
                $factureOrigine->update(['statut' => 'Reprise de biens']);
            } elseif ($nature === 'COR') {
                $factureOrigine->update(['statut' => 'Rectifiée par avoir']);
            } elseif ($factureOrigine->fresh()->aAvoirTotal()) {
                $factureOrigine->update(['statut' => 'Avoir total appliqué']);
            } else {
                $factureOrigine->update(['statut' => 'Avoir partiel']);
            }

            // Gestion de l'impact Trésorerie & Décaissement si le client a un trop-perçu et souhaite un remboursement immédiat
            if ($request->boolean('effectuer_remboursement')) {
                $montantRemb = (float) $request->input('remboursement_montant');
                if ($montantRemb > 0) {
                    $modeRemb = $request->input('remboursement_mode', 'Espèces');
                    $refRemb = $request->input('remboursement_ref') ?: ('Remboursement suite à ' . $facture_avoir->code);

                    \App\Models\Reglement::create([
                        'code' => \App\Models\Reglement::generateCode(),
                        'date' => $request->date ?? now()->toDateString(),
                        'mode' => $modeRemb,
                        'montant' => -$montantRemb, // Décaissement de trésorerie (montant négatif)
                        'reference_paiement' => $refRemb,
                        'observation' => 'Décaissement / Remboursement client consécutif à l\'avoir ' . $facture_avoir->code,
                        'facture_id' => $factureOrigine->id,
                        'users_id' => auth()->id(),
                    ]);

                    \App\Models\AuditLog::enregistrer('CREATE', 'REGLEMENT', "Décaissement de trésorerie de " . number_format($montantRemb, 0, ',', ' ') . " FCFA au client suite à l'avoir {$facture_avoir->code}", $factureOrigine->id);
                }
            }

            \App\Models\AuditLog::enregistrer('CREATE', 'AVOIR', "Création de l'avoir fiscal {$facture_avoir->code} (Nature: {$nature})", $facture_avoir->id);

            $actionType = $request->input('action_type');
            $isAutoSign = (bool) optional(Entete::first())->sfe_automatic_invoice_signing;
            $certifieDirect = false;

            if ($actionType === 'certifier' || $isAutoSign) {
                try {
                    $this->sfeSigner->signAvoir($facture_avoir->fresh());
                    $certifieDirect = true;
                } catch (\Throwable $e) {
                    report($e);
                    \Log::warning("Certification directe de l'avoir échouée", [
                        'facture_avoir_id' => $facture_avoir->id,
                        'message' => $e->getMessage()
                    ]);
                    $notesArr = !empty($facture_avoir->notes) ? (json_decode($facture_avoir->notes, true) ?: []) : [];
                    $notesArr['sfe_error'] = $e->getMessage();
                    $facture_avoir->update([
                        'statut' => 'Erreur SFE',
                        'notes' => json_encode($notesArr, JSON_UNESCAPED_UNICODE)
                    ]);
                }
            }

            return [$facture_avoir, $certifieDirect];
        });

        [$facture_avoir, $certifieDirect] = $facture_avoir_result;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'facture_avoir' => [
                    'id' => $facture_avoir->id,
                    'code' => $facture_avoir->code,
                    'statut' => $facture_avoir->fresh()->statut,
                    'is_certified' => $certifieDirect || !empty($facture_avoir->fresh()->dgi_signature),
                ],
            ]);
        }

        if ($certifieDirect) {
            smilify('success', "Avoir fiscal {$facture_avoir->code} créé et fiscalisé avec succès !");
        } elseif ($facture_avoir->fresh()->statut === 'Erreur SFE') {
            smilify('warning', "Avoir {$facture_avoir->code} enregistré, mais la certification a échoué : " . ($facture_avoir->fresh()->sfe_error ?? 'Vérifiez la connexion MCF'));
        } else {
            smilify('success', "Avoir fiscal {$facture_avoir->code} enregistré avec succès.");
        }

        return redirect()->route('module_ordre_facture.index');
    }

    /**
     * Afficher une facture d'avoir.
     */
    public function show($id)
    {
        $facture_avoir = FactureAvoir::with(['Contribuable', 'Facture', 'User'])->findOrFail($id);
        $elements = ElementFactureAvoir::with(['Produit.tva'])
            ->where('facture_avoir_id', $facture_avoir->id)
            ->get();

        $montant_total = $elements->sum(function ($element) {
            return $element->montant_total ?? ($element->quantite * $element->prix_unitaire);
        });

        $factures = Facture::orderByDesc('id')->get();
        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();

        return view('pages.facture_avoir.show', compact('facture_avoir', 'elements', 'montant_total', 'factures', 'contribuables', 'produits'));
    }

    /**
     * Afficher le formulaire d'édition d'une facture d'avoir.
     */
    public function edit($id)
    {
        $facture_avoir = FactureAvoir::with(['Contribuable', 'Facture'])->findOrFail($id);
        if ($facture_avoir->is_fiscal_locked) {
            abort(423, 'Avoir fiscalisé : modification interdite.');
        }
        $elements = ElementFactureAvoir::with('Produit')
            ->where('facture_avoir_id', $facture_avoir->id)
            ->get();

        $factures = Facture::with('Contribuable')->orderByDesc('id')->get();
        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();
        $natures = SfeInvoiceType::NATURES;

        // Préparer les données factures pour auto-remplissage
        $facturesData = [];
        foreach ($factures as $f) {
            $facturesData[$f->id] = [
                'code' => $f->code,
                'client_id' => $f->Contribuable?->id,
                'client_name' => $f->Contribuable?->assujeti,
                'objet' => $f->objet,
            ];
        }
        $facturesDataJson = json_encode($facturesData);

        return view('pages.facture_avoir.edit', compact('facture_avoir', 'elements', 'factures', 'contribuables', 'produits', 'facturesDataJson', 'natures'));
    }

    /**
     * Mettre à jour la facture d'avoir + ses éléments.
     */
    public function update(Request $request, $id)
    {
        $facture_avoir = FactureAvoir::findOrFail($id);

        if ($facture_avoir->is_fiscal_locked) {
            abort(423, 'Avoir fiscalisé : toute modification est interdite.');
        }

        $request->validate([
            'motif' => 'nullable|in:retour,erreur,annulation,remise,produit_defectueux',
            'nature_avoir' => 'nullable|in:COR,RAN,RAM,RRR',
        ]);

        $nature = SfeInvoiceType::normalizeNature($request->nature_avoir)
            ?: SfeInvoiceType::natureFromMotif($request->motif)
            ?: $facture_avoir->nature_avoir;

        DB::transaction(function () use ($request, $facture_avoir, $nature) {
            $facture_avoir->update([
                'code'             => $request->code ?? $facture_avoir->code,
                'reference'        => $request->reference,
                'date'             => $request->date,
                'statut'           => $request->statut ?? $facture_avoir->statut,
                'objet'            => $request->objet,
                'motif'            => $request->motif,
                'nature_avoir'     => $nature,
                'remise_1'         => $request->remise_1 ?? 0,
                'remise_2'         => $request->remise_2 ?? 0,
                'facture_id'       => $request->facture_id,
                'contribuables_id' => $request->contribuables_id,
            ]);

            ElementFactureAvoir::where('facture_avoir_id', $facture_avoir->id)->delete();
            $this->saveElements($facture_avoir, $request);
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture d\'avoir mise à jour avec succès.');
        }

        return redirect()->route('module_facture_avoir.show', $facture_avoir->id);
    }

    /**
     * Supprimer une facture d'avoir.
     */
    public function destroy($id)
    {
        $facture_avoir = FactureAvoir::findOrFail($id);

        if ($facture_avoir->is_fiscal_locked) {
            if (function_exists('smilify')) {
                smilify('error', 'Avoir fiscalisé : suppression interdite.');
            }
            return redirect()->route('module_facture_avoir.show', $facture_avoir->id);
        }

        DB::transaction(function () use ($facture_avoir) {
            ElementFactureAvoir::where('facture_avoir_id', $facture_avoir->id)->delete();
            $facture_avoir->delete();
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture d\'avoir supprimée.');
        }

        return redirect()->route('module_facture_avoir.index');
    }

    /**
     * Fiscaliser l'avoir via le bridge SFE (FA / EA).
     */
    public function certifier($id)
    {
        $facture_avoir = FactureAvoir::findOrFail($id);

        try {
            $this->sfeSigner->signAvoir($facture_avoir->fresh());
            \App\Models\AuditLog::enregistrer('CERTIFIER', 'AVOIR', "Certification fiscale SFE/MCF de l'avoir {$facture_avoir->code}", $facture_avoir->id);
            if (function_exists('smilify')) {
                smilify('success', 'Avoir fiscalisé et verrouillé avec succès.');
            }
        } catch (\Throwable $e) {
            report($e);
            Log::warning('Fiscalisation avoir refusée', [
                'facture_avoir_id' => $facture_avoir->id,
                'message' => $e->getMessage(),
            ]);
            if (function_exists('smilify')) {
                smilify('error', 'Fiscalisation refusée : ' . $e->getMessage());
            }
        }

        return redirect()->route('module_facture_avoir.show', $facture_avoir->id);
    }

    /**
     * Sauvegarder les éléments de la facture d'avoir et réintégrer le stock si retour de marchandise (RAN).
     */
    private function saveElements($facture_avoir, $request, string $nature = '')
    {
        $produitIds = (array) $request->input('produits_id', []);
        $quantites = (array) $request->input('quantite', []);
        $prixUnitaires = (array) $request->input('prix_unitaire', []);

        foreach ($produitIds as $index => $produitId) {
            if (!$produitId) {
                continue;
            }
            $qte = (float) ($quantites[$index] ?? 0);
            if ($qte <= 0) {
                continue;
            }
            $prix = (float) ($prixUnitaires[$index] ?? 0);
            $montantTotal = $qte * $prix;

            ElementFactureAvoir::create([
                'facture_avoir_id' => $facture_avoir->id,
                'produits_id'      => $produitId,
                'quantite'         => $qte,
                'prix_unitaire'    => $prix,
                'montant_total'    => $montantTotal,
            ]);

            // Retour de marchandise (RAN ou RAM) : Réintégrer le stock physique uniquement s'il s'agit d'un bien
            if (in_array($nature, ['RAN', 'RAM'], true)) {
                $produit = Produit::find($produitId);
                if ($produit && $produit->isBien() && $qte > 0) {
                    $produit->increment('qte', $qte);
                }
            }
        }
    }

    /**
     * Imprimer une facture d'avoir.
     */
    public function print($id)
    {
        $facture_avoir = FactureAvoir::with(['Contribuable.Categorie', 'Facture', 'User'])->findOrFail($id);
        $elements = ElementFactureAvoir::with(['Produit.tva'])
            ->where('facture_avoir_id', $facture_avoir->id)
            ->get();
        $entete = Entete::first();

        // Synthèse fiscale stricte identique aux spécifications DGI / SECeF BF ST 1.0
        $isRrr = strtoupper((string) ($facture_avoir->nature_avoir ?? '')) === 'RRR';
        $tauxBic = $isRrr ? 0.0 : (float) optional(optional($facture_avoir->Facture)->Bic)->taux;

        $total_ht = 0.0;
        $total_ts = 0.0;
        $lignesParGroupe = [];

        foreach ($elements as $element) {
            $qte = (float) $element->quantite;
            $pu = (float) $element->prix_unitaire;
            $montantLigneHt = $qte * $pu;
            $total_ht += $montantLigneHt;

            $produit = $element->Produit;
            $tsUnit = 0.0;
            if ($produit) {
                if ($produit->type_ts === 'POURCENTAGE' && (float) ($produit->pourcentage_ts ?? 0) > 0) {
                    $tsUnit = round($pu * ((float) $produit->pourcentage_ts / 100.0), 2);
                } elseif (!$isRrr) {
                    $tsUnit = (float) ($produit->montant_ts ?? 0);
                }
            }
            $tsLigne = $tsUnit * $qte;
            $total_ts += $tsLigne;

            $groupe = strtoupper((string) optional(optional($produit)->tva)->code ?: 'A');
            $tauxTva = $groupe === 'A' ? 0.0 : (float) optional(optional($produit)->tva)->taux;

            if (!isset($lignesParGroupe[$groupe])) {
                $lignesParGroupe[$groupe] = [
                    'groupe' => $groupe,
                    'taux' => $tauxTva,
                    'ht_brut' => 0.0,
                    'ts' => 0.0,
                ];
            }
            $lignesParGroupe[$groupe]['ht_brut'] += $montantLigneHt;
            $lignesParGroupe[$groupe]['ts'] += $tsLigne;
        }

        $remise_1 = (float) ($facture_avoir->remise_1 ?? 0);
        $remise_2 = (float) ($facture_avoir->remise_2 ?? 0);
        $remise_totale = $remise_1 + $remise_2;
        $net_commercial = max(0, $total_ht - $remise_totale);
        $prorata = $total_ht > 0 ? ($net_commercial / $total_ht) : 1.0;

        $tva_groups = [];
        $total_taxe = 0.0;
        ksort($lignesParGroupe);
        foreach ($lignesParGroupe as $grp => $info) {
            $baseHt = round($info['ht_brut'] * $prorata, 2);
            $baseTva = round($baseHt + $info['ts'], 2);
            $montantTva = round($baseTva * ($info['taux'] / 100), 2);
            $total_taxe += $montantTva;

            $tva_groups[$grp] = [
                'groupe' => $grp,
                'taux' => $info['taux'],
                'base' => $baseHt,
                'base_tva' => $baseTva,
                'taxe' => $montantTva,
                'ts' => $info['ts'],
            ];
        }

        $total_ttc = $net_commercial + $total_ts + $total_taxe;

        // BIC (PSVB) : strictement exclu sur les avoirs financiers RRR, appliqué sur biens matériels uniquement
        $bic_groups = [];
        $total_bic = 0.0;
        if ($tauxBic > 0 && !$isRrr) {
            $base_psvb_biens = 0.0;
            foreach ($elements as $element) {
                $produit = $element->Produit;
                if ($produit && $produit->isBien()) {
                    $qte = (float) $element->quantite;
                    $pu = (float) $element->prix_unitaire;
                    $ligneHt = $qte * $pu * $prorata;
                    $tsUnit = 0.0;
                    if ($produit->type_ts === 'POURCENTAGE' && (float) ($produit->pourcentage_ts ?? 0) > 0) {
                        $tsUnit = round($pu * ((float) $produit->pourcentage_ts / 100.0), 2);
                    } else {
                        $tsUnit = (float) ($produit->montant_ts ?? 0);
                    }
                    $tsLigne = $tsUnit * $qte;
                    $tauxTvaLigne = (float) optional(optional($produit)->tva)->taux;
                    $baseTvaLigne = $ligneHt + $tsLigne;
                    $tvaLigne = $baseTvaLigne * ($tauxTvaLigne / 100.0);
                    $base_psvb_biens += ($baseTvaLigne + $tvaLigne);
                }
            }
            if ($base_psvb_biens > 0) {
                $total_bic = round($base_psvb_biens * ($tauxBic / 100), 2);
                $bic_groups[] = [
                    'taux' => $tauxBic,
                    'base_ttc' => round($base_psvb_biens, 2),
                    'montant' => $total_bic,
                ];
            }
        }

        $montant_total = $total_ttc + $total_bic;

        // Certification FA/EA de l'avoir (pas celle de l'original seule).
        $certif = $facture_avoir->certification_active;
        $certifOriginal = optional($facture_avoir->Facture)->certification_active;
        $natureLabel = SfeInvoiceType::NATURES[$facture_avoir->nature_avoir ?? '']
            ?? ($facture_avoir->nature_avoir ?: null);
        $isExportAvoir = $certif && $certif->type_facture === 'EA';

        return view('pages.facture_avoir.print', compact(
            'facture_avoir', 'elements', 'entete', 'certif', 'certifOriginal',
            'natureLabel', 'isExportAvoir',
            'total_ht', 'remise_1', 'remise_2', 'net_commercial',
            'tva_groups', 'total_taxe',
            'bic_groups', 'total_bic',
            'total_ttc', 'montant_total'
        ));
    }
}
