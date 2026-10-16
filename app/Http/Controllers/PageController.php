<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Facture;
use App\Models\ElementFacture;
use App\Models\Contribuable;
use App\Models\Categorie;
use App\Models\Service;
use App\Models\Reglement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{

    public function auth()
    {
        return view('auth.login');
    }

    public function dashboard()
    {
        $today         = Carbon::today();
        $moisCourant   = Carbon::now()->month;
        $anneeCourante = Carbon::now()->year;

        // ── Toutes les factures avec relations pour les accessors ──
        $collection = Facture::with([
            'Contribuable', 'Service', 'Categorie',
            'ElementFactures.Produit.tva', 'Bic',
            'Reglements',
        ])->orderByDesc('id')->get();

        // ── Factures du jour ──
        $recettes = Facture::with(['Contribuable', 'Service'])
            ->where('date', $today->toDateString())
            ->orderByDesc('id')
            ->get();

        // ── KPI : répartition par statut de paiement ──
        $nombreFactures           = $collection->count();
        $nombreFacturesImpayees   = $collection->filter(fn($f) => $f->statut_paiement === 'impayée')->count();
        $nombreFacturesSoldees    = $collection->filter(fn($f) => $f->statut_paiement === 'soldée')->count();
        $nombreFacturesPartielles = $collection->filter(fn($f) => $f->statut_paiement === 'partielle')->count();

        // ── KPI financiers – mois courant (net à payer = TTC + BIC) ──
        $calcNetAPayer = function ($facture) {
            $total = 0.0;
            foreach ($facture->ElementFactures as $el) {
                $ht    = (float) $el->quantite * (float) $el->prix_unitaire;
                $tva   = (float) optional(optional($el->Produit)->tva)->taux;
                $ttc   = $ht + ($ht * $tva / 100);
                $bic   = $ttc * ((float) optional($facture->Bic)->taux) / 100;
                $total += $ttc + $bic;
            }
            return $total;
        };

        $totalCaMois = $collection
            ->filter(fn($f) => $f->date &&
                \Carbon\Carbon::parse($f->date)->month == $moisCourant &&
                \Carbon\Carbon::parse($f->date)->year  == $anneeCourante)
            ->sum($calcNetAPayer);

        $totalEncaissementsMois = (float) DB::table('reglements')
            ->whereBetween('date', [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ])
            ->sum('montant');

        $totalDepensesMois = (float) DB::table('depenses')
            ->whereBetween('date', [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ])
            ->sum('montant');

        // ── KPI financiers – global (net à payer = TTC + BIC) ──
        $totalCaGlobal = $collection->sum($calcNetAPayer);

        $totalEncaissements = (float) DB::table('reglements')->sum('montant');

        $tauxRecouvrement = $totalCaGlobal > 0
            ? round(($totalEncaissements / $totalCaGlobal) * 100, 1)
            : 0;

        // ── KPI : comptages ──
        $nombreClientsActifs = DB::table('factures')->distinct('contribuable_id')->count('contribuable_id');
        $nombreProformas     = DB::table('facture_proformas')->count();
        $nombreBordereaux    = DB::table('bordereau_livraisons')->count();
        $nombreFournisseurs  = DB::table('fournisseurs')->count();

        // ── Statistiques par service et par métier (requête unique) ──
        $statsService = [];
        $statsMetier  = [];

        DB::table('element_factures')
            ->join('factures', 'element_factures.facture_id', '=', 'factures.id')
            ->leftJoin('services', 'factures.service_id', '=', 'services.id')
            ->leftJoin('contribuables', 'factures.contribuable_id', '=', 'contribuables.id')
            ->leftJoin('categories', 'contribuables.categories_id', '=', 'categories.id')
            ->select(
                'services.libelle as service_libelle',
                'categories.libelle as categorie_libelle',
                DB::raw('SUM(element_factures.quantite * element_factures.prix_unitaire) as total')
            )
            ->groupBy('services.libelle', 'categories.libelle')
            ->get()
            ->each(function ($row) use (&$statsService, &$statsMetier) {
                $service = $row->service_libelle  ?? 'Non défini';
                $metier  = $row->categorie_libelle ?? 'Non définie';
                $total   = (float) $row->total;

                $statsService[$service] = ($statsService[$service] ?? 0) + $total;
                $statsMetier[$metier]   = ($statsMetier[$metier]   ?? 0) + $total;
            });

        // ── Évolution mensuelle – 6 derniers mois ──
        $labels               = [];
        $evolutionCa          = [];
        $evolutionEncaissements = [];
        $moisFr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

        $debutEvolution = Carbon::now()->subMonths(5)->startOfMonth();
        $finEvolution   = Carbon::now()->endOfMonth();

        // YEAR()/MONTH() are MySQL-only; PostgreSQL needs EXTRACT().
        $driver = DB::getDriverName();
        $yearExprFacture  = $driver === 'pgsql'
            ? 'EXTRACT(YEAR FROM factures.date)::int'
            : 'YEAR(factures.date)';
        $monthExprFacture = $driver === 'pgsql'
            ? 'EXTRACT(MONTH FROM factures.date)::int'
            : 'MONTH(factures.date)';
        $yearExprReg      = $driver === 'pgsql'
            ? 'EXTRACT(YEAR FROM date)::int'
            : 'YEAR(date)';
        $monthExprReg     = $driver === 'pgsql'
            ? 'EXTRACT(MONTH FROM date)::int'
            : 'MONTH(date)';

        $caParMois = DB::table('element_factures')
            ->join('factures', 'element_factures.facture_id', '=', 'factures.id')
            ->whereBetween('factures.date', [$debutEvolution->toDateString(), $finEvolution->toDateString()])
            ->selectRaw("{$yearExprFacture} as annee, {$monthExprFacture} as mois")
            ->selectRaw('SUM(element_factures.quantite * element_factures.prix_unitaire) as total')
            ->groupByRaw("{$yearExprFacture}, {$monthExprFacture}")
            ->get()
            ->keyBy(fn($row) => sprintf('%04d-%02d', $row->annee, $row->mois));

        $encaissementsParMois = DB::table('reglements')
            ->whereBetween('date', [$debutEvolution->toDateString(), $finEvolution->toDateString()])
            ->selectRaw("{$yearExprReg} as annee, {$monthExprReg} as mois")
            ->selectRaw('SUM(montant) as total')
            ->groupByRaw("{$yearExprReg}, {$monthExprReg}")
            ->get()
            ->keyBy(fn($row) => sprintf('%04d-%02d', $row->annee, $row->mois));

        for ($i = 5; $i >= 0; $i--) {
            $month   = Carbon::now()->subMonths($i);
            $labels[] = $moisFr[$month->month - 1] . ' ' . $month->year;

            $periode = $month->format('Y-m');
            $ca       = (float) optional($caParMois->get($periode))->total;
            $enc      = (float) optional($encaissementsParMois->get($periode))->total;

            $evolutionCa[]            = round($ca, 2);
            $evolutionEncaissements[] = round($enc, 2);
        }

        // ── Derniers règlements ──
        $derniersReglements = Reglement::with(['Facture.Contribuable'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('pages.dashboard.index', compact(
            'collection', 'recettes', 'statsService', 'statsMetier',
            'nombreFactures', 'nombreFacturesImpayees', 'nombreFacturesSoldees', 'nombreFacturesPartielles',
            'totalCaMois', 'totalEncaissementsMois', 'totalDepensesMois',
            'totalCaGlobal', 'totalEncaissements', 'tauxRecouvrement',
            'nombreClientsActifs', 'nombreProformas', 'nombreBordereaux', 'nombreFournisseurs',
            'evolutionCa', 'evolutionEncaissements', 'labels',
            'derniersReglements'
        ));
    }

    public function solde_client(\Illuminate\Http\Request $request)
    {
        $contribuables = Contribuable::orderBy('assujeti')->get();

        $contribuableId = $request->input('contribuable_id');
        $dateDebut      = $request->input('date_debut');
        $dateFin        = $request->input('date_fin');

        $resultats          = collect();
        $clientCourant      = null;
        $grandTotalFacture  = 0;
        $grandTotalRegle    = 0;
        $grandTotalSolde    = 0;

        if ($request->has('contribuable_id') || $request->has('date_debut') || $request->has('date_fin')) {

            $query = Facture::with(['Contribuable', 'Service', 'Categorie', 'ElementFactures.Produit.tva'])
                ->when($contribuableId, fn($q) => $q->where('contribuable_id', $contribuableId))
                ->when($dateDebut, fn($q) => $q->whereDate('date', '>=', $dateDebut))
                ->when($dateFin, fn($q) => $q->whereDate('date', '<=', $dateFin))
                ->orderBy('date');

            $factures = $query->get();

            // Calcul du montant total TTC par facture
            $factures->transform(function ($facture) use (&$grandTotalFacture, &$grandTotalRegle, &$grandTotalSolde) {
                $totalHt   = 0;
                $totalTaxe = 0;

                foreach ($facture->ElementFactures as $element) {
                    $quantite = (float) $element->quantite;
                    $prix     = (float) $element->prix_unitaire;
                    $ht       = $quantite * $prix;
                    $taux     = (float) optional(optional($element->Produit)->tva)->taux;

                    $totalHt   += $ht;
                    $totalTaxe += $ht * $taux / 100;
                }

                $facture->montant_ht  = $totalHt;
                $facture->montant_tva = $totalTaxe;
                $facture->montant_ttc = $totalHt + $totalTaxe;

                // Tant qu'il n'y a pas de table de règlements, le montant réglé
                // est déterminé par le statut de la facture.
                $facture->montant_regle = strtolower(trim($facture->statut ?? '')) === 'reglée'
                    ? $facture->montant_ttc
                    : 0;

                $facture->solde = $facture->montant_ttc - $facture->montant_regle;

                $grandTotalFacture += $facture->montant_ttc;
                $grandTotalRegle   += $facture->montant_regle;
                $grandTotalSolde   += $facture->solde;

                return $facture;
            });

            // Regroupement par client
            $resultats = $factures->groupBy('contribuable_id')->map(function ($grp) {
                return (object) [
                    'client'        => optional($grp->first())->Contribuable,
                    'factures'      => $grp,
                    'total_facture' => $grp->sum('montant_ttc'),
                    'total_regle'   => $grp->sum('montant_regle'),
                    'solde'         => $grp->sum('solde'),
                ];
            })->values();

            if ($contribuableId) {
                $clientCourant = Contribuable::find($contribuableId);
            }
        }

        return view('pages.etats.solde_client', compact(
            'contribuables', 'resultats', 'clientCourant',
            'contribuableId', 'dateDebut', 'dateFin',
            'grandTotalFacture', 'grandTotalRegle', 'grandTotalSolde'
        ));
    }

    public function print_solde_client(\Illuminate\Http\Request $request)
    {
        $contribuableId = $request->input('contribuable_id');
        $dateDebut      = $request->input('date_debut');
        $dateFin        = $request->input('date_fin');

        $clientCourant = $contribuableId ? Contribuable::find($contribuableId) : null;

        $factures = Facture::with(['Contribuable', 'ElementFactures.Produit.tva', 'Bic', 'Reglements'])
            ->when($contribuableId, fn($q) => $q->where('contribuable_id', $contribuableId))
            ->when($dateDebut, fn($q) => $q->whereDate('date', '>=', $dateDebut))
            ->when($dateFin, fn($q) => $q->whereDate('date', '<=', $dateFin))
            ->orderBy('date')
            ->get();

        $grandTotalFacture = 0;
        $grandTotalRegle   = 0;
        $grandTotalSolde   = 0;

        $factures->transform(function ($facture) use (&$grandTotalFacture, &$grandTotalRegle, &$grandTotalSolde) {
            // Utilise l'accessor montant_total du modèle (TTC + BIC)
            $facture->montant_ttc   = (float) $facture->montant_total;
            $facture->montant_regle = (float) $facture->montant_paye;
            $ristourne              = (float) ($facture->ristourne ?? 0);
            $facture->solde         = max(0, $facture->montant_ttc - $ristourne - $facture->montant_regle);

            $grandTotalFacture += $facture->montant_ttc;
            $grandTotalRegle   += $facture->montant_regle;
            $grandTotalSolde   += $facture->solde;

            return $facture;
        });

        $entete = \App\Models\Entete::first();

        return view('pages.etats.print_solde_client', compact(
            'factures', 'clientCourant', 'dateDebut', 'dateFin',
            'grandTotalFacture', 'grandTotalRegle', 'grandTotalSolde', 'entete'
        ));
    }

    public function etat_taxe(\Illuminate\Http\Request $request)
    {
        $tvas = \App\Models\Tva::orderBy('taux')->get();
        $bics = \App\Models\Bic::orderBy('taux')->get();

        $tvaId     = $request->input('tva_id');
        $bicId     = $request->input('bic_id');
        $dateDebut = $request->input('date_debut');
        $dateFin   = $request->input('date_fin');

        $factures        = collect();
        $tvaGroups       = [];
        $bicGroups       = [];
        $totalHt         = 0;
        $totalTva        = 0;
        $totalBic        = 0;
        $totalTtc        = 0;
        $hasFilter       = $request->has('tva_id') || $request->has('bic_id')
                           || $request->has('date_debut') || $request->has('date_fin');

        if ($hasFilter) {
            $factures = Facture::with(['Contribuable', 'ElementFactures.Produit.tva', 'Bic'])
                ->when($dateDebut, fn($q) => $q->whereDate('date', '>=', $dateDebut))
                ->when($dateFin, fn($q) => $q->whereDate('date', '<=', $dateFin))
                ->orderBy('date')
                ->get();

            $tvaIdInt = $tvaId ? (int) $tvaId : null;
            $bicIdInt = $bicId ? (int) $bicId : null;

            $factures = $factures->map(function ($facture) use ($tvaIdInt, $bicIdInt, &$tvaGroups, &$bicGroups, &$totalHt, &$totalTva, &$totalBic, &$totalTtc) {
                $factHt  = 0; $factTva = 0; $factBic = 0; $factTtc = 0;

                foreach ($facture->ElementFactures as $el) {
                    $produit  = $el->Produit;
                    $tva      = $produit ? $produit->tva : null;
                    $bic      = $produit ? $produit->bic : null;
                    $tauxTva  = (float) optional($tva)->taux;
                    $tauxBic  = (float) optional($bic)->taux;

                    // Filtres : on ne retient l'élément que s'il correspond
                    if ($tvaIdInt && (!$tva || $tva->id !== $tvaIdInt)) continue;
                    if ($bicIdInt && (!$bic || $bic->id !== $bicIdInt)) continue;

                    $brut    = (float) $el->quantite * (float) $el->prix_unitaire;
                    $ht      = $brut;
                    $mtTva   = $ht * $tauxTva / 100;
                    $ttc     = $ht + $mtTva;
                    $mtBic   = $ttc * $tauxBic / 100;

                    $factHt  += $ht;
                    $factTva += $mtTva;
                    $factTtc += $ttc;
                    $factBic += $mtBic;

                    // Groupes TVA
                    if ($tva) {
                        $key = $tva->id;
                        if (!isset($tvaGroups[$key])) {
                            $tvaGroups[$key] = [
                                'intitule' => $tva->intitule,
                                'taux'     => $tauxTva,
                                'base_ht'  => 0,
                                'montant'  => 0,
                            ];
                        }
                        $tvaGroups[$key]['base_ht'] += $ht;
                        $tvaGroups[$key]['montant'] += $mtTva;
                    }

                    // Groupes BIC
                    if ($bic) {
                        $key = $bic->id;
                        if (!isset($bicGroups[$key])) {
                            $bicGroups[$key] = [
                                'intitule' => $bic->intitule,
                                'taux'     => $tauxBic,
                                'base_ttc' => 0,
                                'montant'  => 0,
                            ];
                        }
                        $bicGroups[$key]['base_ttc'] += $ttc;
                        $bicGroups[$key]['montant']  += $mtBic;
                    }
                }

                $facture->total_ht  = $factHt;
                $facture->total_tva = $factTva;
                $facture->total_ttc = $factTtc;
                $facture->total_bic = $factBic;

                $totalHt  += $factHt;
                $totalTva += $factTva;
                $totalTtc += $factTtc;
                $totalBic += $factBic;

                return $facture;
            })->filter(fn($f) => ($f->total_ht + $f->total_bic) > 0)->values();
        }

        return view('pages.etats.etat_taxe', compact(
            'tvas', 'bics', 'tvaId', 'bicId', 'dateDebut', 'dateFin',
            'factures', 'tvaGroups', 'bicGroups',
            'totalHt', 'totalTva', 'totalBic', 'totalTtc'
        ));
    }

    public function print_etat_taxe(\Illuminate\Http\Request $request)
    {
        $tvaId     = $request->input('tva_id');
        $bicId     = $request->input('bic_id');
        $dateDebut = $request->input('date_debut');
        $dateFin   = $request->input('date_fin');

        $tvaLibelle = $tvaId ? optional(\App\Models\Tva::find($tvaId))->intitule : 'Toutes';
        $bicLibelle = $bicId ? optional(\App\Models\Bic::find($bicId))->intitule : 'Tous';

        $totalHt = 0; $totalTva = 0; $totalBic = 0; $totalTtc = 0;

        $factures = Facture::with(['Contribuable', 'ElementFactures.Produit.tva', 'ElementFactures.Produit.bic'])
            ->when($dateDebut, fn($q) => $q->whereDate('date', '>=', $dateDebut))
            ->when($dateFin, fn($q) => $q->whereDate('date', '<=', $dateFin))
            ->orderBy('date')
            ->get();

        $tvaIdInt = $tvaId ? (int) $tvaId : null;
        $bicIdInt = $bicId ? (int) $bicId : null;

        $factures = $factures->map(function ($facture) use ($tvaIdInt, $bicIdInt, &$totalHt, &$totalTva, &$totalBic, &$totalTtc) {
            $factHt = 0; $factTva = 0; $factBic = 0; $factTtc = 0;

            foreach ($facture->ElementFactures as $el) {
                $produit = $el->Produit;
                $tva = $produit ? $produit->tva : null;
                $bic = $produit ? $produit->bic : null;

                if ($tvaIdInt && (!$tva || $tva->id !== $tvaIdInt)) continue;
                if ($bicIdInt && (!$bic || $bic->id !== $bicIdInt)) continue;

                $tauxTva = (float) optional($tva)->taux;
                $tauxBic = (float) optional($bic)->taux;

                $brut  = (float) $el->quantite * (float) $el->prix_unitaire;
                $ht    = $brut;
                $mtTva = $ht * $tauxTva / 100;
                $ttc   = $ht + $mtTva;
                $mtBic = $ttc * $tauxBic / 100;

                $factHt  += $ht;
                $factTva += $mtTva;
                $factTtc += $ttc;
                $factBic += $mtBic;
            }

            $facture->total_ht  = $factHt;
            $facture->total_tva = $factTva;
            $facture->total_ttc = $factTtc;
            $facture->total_bic = $factBic;

            $totalHt  += $factHt;
            $totalTva += $factTva;
            $totalTtc += $factTtc;
            $totalBic += $factBic;

            return $facture;
        })->filter(fn($f) => ($f->total_ht + $f->total_bic) > 0)->values();

        $entete = \App\Models\Entete::first();

        return view('pages.etats.print_etat_taxe', compact(
            'factures', 'tvaLibelle', 'bicLibelle', 'dateDebut', 'dateFin',
            'totalHt', 'totalTva', 'totalBic', 'totalTtc', 'entete'
        ));
    }

    public function etat_facture_metier(\Illuminate\Http\Request $request)
    {
        $categories = Categorie::orderBy('libelle')->get();

        $categorieId = $request->input('categorie_id');
        $dateDebut   = $request->input('date_debut');
        $dateFin     = $request->input('date_fin');

        $resultats         = collect();
        $grandTotalFacture = 0;
        $grandTotalRegle   = 0;
        $grandTotalSolde   = 0;

        if ($request->has('categorie_id') || $request->has('date_debut') || $request->has('date_fin')) {

            $factures = Facture::with(['Contribuable', 'Service', 'Categorie', 'ElementFactures.Produit.tva'])
                ->when($categorieId, fn($q) => $q->where('categorie_id', $categorieId))
                ->when($dateDebut, fn($q) => $q->whereDate('date', '>=', $dateDebut))
                ->when($dateFin, fn($q) => $q->whereDate('date', '<=', $dateFin))
                ->orderBy('date')
                ->get();

            $factures->transform(function ($facture) use (&$grandTotalFacture, &$grandTotalRegle, &$grandTotalSolde) {
                $totalHt   = 0;
                $totalTaxe = 0;

                foreach ($facture->ElementFactures as $element) {
                    $quantite = (float) $element->quantite;
                    $prix     = (float) $element->prix_unitaire;
                    $ht       = $quantite * $prix;
                    $taux     = (float) optional(optional($element->Produit)->tva)->taux;

                    $totalHt   += $ht;
                    $totalTaxe += $ht * $taux / 100;
                }

                $facture->montant_ht  = $totalHt;
                $facture->montant_tva = $totalTaxe;
                $facture->montant_ttc = $totalHt + $totalTaxe;

                $facture->montant_regle = strtolower(trim($facture->statut ?? '')) === 'reglée'
                    ? $facture->montant_ttc
                    : 0;

                $facture->solde = $facture->montant_ttc - $facture->montant_regle;

                $grandTotalFacture += $facture->montant_ttc;
                $grandTotalRegle   += $facture->montant_regle;
                $grandTotalSolde   += $facture->solde;

                return $facture;
            });

            // Regroupement par ligne métier (categorie)
            $resultats = $factures->groupBy('categorie_id')->map(function ($grp) {
                return (object) [
                    'categorie'     => optional($grp->first())->Categorie,
                    'factures'      => $grp,
                    'total_facture' => $grp->sum('montant_ttc'),
                    'total_regle'   => $grp->sum('montant_regle'),
                    'solde'         => $grp->sum('solde'),
                ];
            })->values();
        }

        return view('pages.etats.etat_facture_metier', compact(
            'categories', 'resultats',
            'categorieId', 'dateDebut', 'dateFin',
            'grandTotalFacture', 'grandTotalRegle', 'grandTotalSolde'
        ));
    }

    public function aide_support()
    {
        return view('pages.aide_support.index');
    }

    public function etat_facture_service(\Illuminate\Http\Request $request)
    {
        $services = Service::orderBy('libelle')->get();

        $serviceId = $request->input('service_id');
        $dateDebut = $request->input('date_debut');
        $dateFin   = $request->input('date_fin');

        $resultats         = collect();
        $grandTotalFacture = 0;
        $grandTotalRegle   = 0;
        $grandTotalSolde   = 0;

        if ($request->has('service_id') || $request->has('date_debut') || $request->has('date_fin')) {

            $factures = Facture::with(['Contribuable', 'Service', 'Categorie', 'ElementFactures.Produit.tva'])
                ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
                ->when($dateDebut, fn($q) => $q->whereDate('date', '>=', $dateDebut))
                ->when($dateFin, fn($q) => $q->whereDate('date', '<=', $dateFin))
                ->orderBy('date')
                ->get();

            $factures->transform(function ($facture) use (&$grandTotalFacture, &$grandTotalRegle, &$grandTotalSolde) {
                $totalHt   = 0;
                $totalTaxe = 0;

                foreach ($facture->ElementFactures as $element) {
                    $quantite = (float) $element->quantite;
                    $prix     = (float) $element->prix_unitaire;
                    $ht       = $quantite * $prix;
                    $taux     = (float) optional(optional($element->Produit)->tva)->taux;

                    $totalHt   += $ht;
                    $totalTaxe += $ht * $taux / 100;
                }

                $facture->montant_ht  = $totalHt;
                $facture->montant_tva = $totalTaxe;
                $facture->montant_ttc = $totalHt + $totalTaxe;

                $facture->montant_regle = strtolower(trim($facture->statut ?? '')) === 'reglée'
                    ? $facture->montant_ttc
                    : 0;

                $facture->solde = $facture->montant_ttc - $facture->montant_regle;

                $grandTotalFacture += $facture->montant_ttc;
                $grandTotalRegle   += $facture->montant_regle;
                $grandTotalSolde   += $facture->solde;

                return $facture;
            });

            // Regroupement par ligne de service
            $resultats = $factures->groupBy('service_id')->map(function ($grp) {
                return (object) [
                    'service'       => optional($grp->first())->Service,
                    'factures'      => $grp,
                    'total_facture' => $grp->sum('montant_ttc'),
                    'total_regle'   => $grp->sum('montant_regle'),
                    'solde'         => $grp->sum('solde'),
                ];
            })->values();
        }

        return view('pages.etats.etat_facture_service', compact(
            'services', 'resultats',
            'serviceId', 'dateDebut', 'dateFin',
            'grandTotalFacture', 'grandTotalRegle', 'grandTotalSolde'
        ));
    }
}
