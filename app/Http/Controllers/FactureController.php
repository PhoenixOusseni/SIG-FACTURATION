<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFactureRequest;
use App\Models\Facture;
use App\Models\ElementFacture;
use App\Models\Contribuable;
use App\Models\Produit;
use App\Models\Entete;
use App\Models\Typefacture;
use App\Models\ModePaie;
use App\Models\Bic;
use App\Services\StockService;
use App\Services\SFE\InvoiceSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    protected $sfeSigner;

    public function __construct(InvoiceSigningService $sfeSigner)
    {
        $this->sfeSigner = $sfeSigner;
    }
    /**
     * Liste des factures de l'utilisateur connecté.
     */
    public function index()
    {
        $collection = Facture::with([
            'Contribuable.Categorie',
            'Service',
            'Categorie',
            'Typefacture',
            'ElementFactures.Produit.tva',
            'McfCertifications',
            'Reglements.User',
            'FactureAvoirs.ElementFactureAvoirs.Produit.tva',
            'FactureProforma'
        ])
            ->orderByDesc('id')
            ->get();

        $proformas = \App\Models\FactureProforma::with(['Contribuable', 'ElementFactureProformas.Produit', 'Facture', 'BordereauLivraison'])
            ->orderByDesc('id')
            ->get();

        $avoirs = \App\Models\FactureAvoir::with(['Contribuable', 'Facture', 'ElementFactureAvoirs.Produit'])
            ->orderByDesc('id')
            ->get();

        $bordereaux = \App\Models\BordereauLivraison::with(['Contribuable', 'Service', 'FactureProforma', 'Facture', 'ElementBordereauLivraisons.Produit'])
            ->orderByDesc('id')
            ->get();

        // Factures certifiées pour le modal d'avoir (uniquement celles pouvant encore subir un avoir)
        $facturesCertifiees = $collection->filter(function ($f) {
            return $f->isCertified() && $f->peutSubirAvoir();
        })->values();

        // Factures non soldées pour le modal de règlement
        $facturesImpayees = $collection->filter(function ($f) {
            return $f->reste_a_payer > 0.009;
        })->values();

        // Factures en erreur MCF
        $nbErreursMcf = $collection->where('statut', 'Erreur MCF')->count();

        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::with('tva')->where('is_active', true)->orderBy('code')->get();
        $produitsData = $produits->keyBy('id')->map(function ($produit) {
            return [
                'code' => $produit->code,
                'designation' => $produit->designation,
                'label_fiscal' => $produit->label_fiscal,
                'prix_unitaire' => $produit->prix_unitaire,
                'prix_unitaire_ttc' => $produit->prix_unitaire_ttc,
                'qte' => $produit->qte,
                'is_service' => $produit->isService(),
                'type_article' => $produit->type_dgi,
                'taux_tva' => (float) optional($produit->tva)->taux,
                'groupe_tva' => optional($produit->tva)->code ?: 'A',
                'type_ts' => $produit->type_ts ?? 'AUCUNE',
                'montant_ts' => (float) ($produit->montant_ts ?? 0),
                'pourcentage_ts' => (float) ($produit->pourcentage_ts ?? 0),
            ];
        });

        // FA/EA are certified via FactureAvoir, not the sales form.
        $typefactures = Typefacture::whereIn('code', ['FV', 'FT', 'EV', 'ET'])->orderBy('code')->get();
        $modepaies = ModePaie::orderBy('code')->get();
        $bics = Bic::orderBy('code')->get();
        $categories = \App\Models\Categorie::orderBy('code')->get();
        $tvas = \App\Models\Tva::orderBy('code')->get();
        $services = \App\Models\Service::orderBy('nom')->get();
        $entete = Entete::singleton();

        // Détection de la connexion réelle au boîtier MCF Eltrade / Émulateur
        $snapshotService = app(\App\Services\SFE\StatusSnapshotService::class);
        $snapshotData = $snapshotService->current();
        $isSimulation = (strtoupper($entete->port_com ?? '') === 'SIMULATION');
        $sfeConnected = $isSimulation || (($snapshotData['level'] ?? '') === 'connected' && !empty($snapshotData['device']['succes']));

        return view('pages.facture.index', compact(
            'collection',
            'proformas',
            'avoirs',
            'bordereaux',
            'facturesCertifiees',
            'facturesImpayees',
            'nbErreursMcf',
            'contribuables',
            'produits',
            'produitsData',
            'typefactures',
            'modepaies',
            'bics',
            'categories',
            'tvas',
            'services',
            'entete',
            'sfeConnected'
        ));
    }

    /**
     * Liste de toutes les factures (vue "liste.blade.php").
     */
    public function all_facture()
    {
        $collection = Facture::with(['Contribuable', 'Service', 'Categorie', 'Typefacture', 'ModePaie', 'Bic'])
            ->orderByDesc('id')
            ->get();

        return view('pages.facture.liste', compact('collection'));
    }

    /**
     * Enregistrer une nouvelle facture + ses éléments.
     */
    public function store(StoreFactureRequest $request)
    {
        // Vérification du stock AVANT toute création (bloquant) — agrégé par produit
        $produitIds  = array_values(array_filter((array) $request->input('produits_id', [])));
        $quantites   = (array) $request->input('quantite', []);
        $requestedByProduct = [];

        foreach ($produitIds as $i => $produitId) {
            $qte = (float) ($quantites[$i] ?? 0);
            if ($qte <= 0 || empty($produitId)) {
                continue;
            }
            $key = (string) $produitId;
            $requestedByProduct[$key] = ($requestedByProduct[$key] ?? 0) + $qte;
        }

        $stockErrors = [];
        foreach ($requestedByProduct as $produitId => $qteDemandee) {
            $produit = Produit::find($produitId);
            // Ne contrôler le stock QUE pour les biens physiques (les services n'ont pas d'inventaire)
            if ($produit && $produit->isBien() && (float) ($produit->qte ?? 0) < $qteDemandee) {
                $dispo = number_format((float) ($produit->qte ?? 0), 2, ',', ' ');
                $stockErrors[] = "« {$produit->designation} » — stock disponible : {$dispo} | demandé : {$qteDemandee}";
            }
        }

        if (!empty($stockErrors)) {
            $message = 'Stock insuffisant : ' . implode(' — ', $stockErrors);
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message, 'errors' => $stockErrors], 422);
            }
            return redirect()->back()->withInput()->with('form_error', $message);
        }

        try {
            DB::beginTransaction();

            // Générer automatiquement le code de facture (FAC-00001/2026)
            $year = date('Y');
            $lastFacture = Facture::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastFacture && preg_match('/(?:FACT|FAC)-(\d+)/', $lastFacture->code, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
            $generatedCode = 'FACT-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            // Compilation des 9 lignes libres de commentaires DGI (SECeF ST 1.0)
            $notesData = [];
            for ($k = 1; $k <= 9; $k++) {
                $letter = chr(96 + $k); // a, b, c, ...
                $val = $request->input("comment_{$k}") ?? $request->input("comment_{$letter}");
                if (!empty($val)) {
                    $notesData["Ligne {$k}"] = trim($val);
                }
            }
            if ($request->filled('mention_legale')) {
                $notesData['mention'] = trim($request->input('mention_legale'));
            }
            if ($request->filled('notes_libres')) {
                $notesData['notes_libres'] = trim($request->input('notes_libres'));
            }
            if ($request->filled('mode_prix')) {
                $notesData['mode_prix'] = strtoupper(trim($request->input('mode_prix')));
            }
            $notesJson = !empty($notesData) ? json_encode($notesData, JSON_UNESCAPED_UNICODE) : null;

            $proformaId = $request->filled('facture_proforma_id') ? (int) $request->input('facture_proforma_id') : null;

            $facture = Facture::create([
                'reference'           => $request->reference,
                'date'                => $request->date ?? date('Y-m-d'),
                'code'                => $generatedCode,
                'contribuable_id'     => $request->contribuable_id,
                'service_id'          => $request->service_id,
                'user_id'             => $request->user_id ?? auth()->id(),
                'typefacture_id'      => $request->typefacture_id ?? null,
                'modepaie_id'         => $request->modepaie_id ?? null,
                'bic_id'              => $request->bic_id ?? null,
                'remise_1'            => $request->remise_1 ?? 0,
                'remise_2'            => $request->remise_2 ?? 0,
                'facture_proforma_id' => $proformaId,
                'notes'               => $notesJson,
            ]);

            if ($proformaId) {
                \App\Models\FactureProforma::where('id', $proformaId)->update(['statut' => 'transformée']);
            }

            if ($request->has('produits_id') && is_array($request->produits_id)) {
                foreach ($request->produits_id as $index => $produitId) {
                    if (!empty($produitId)) {
                        $quantite     = (float) ($request->quantite[$index] ?? 0);
                        $prixUnitaire = (float) ($request->prix_unitaire[$index] ?? 0);

                        ElementFacture::create([
                            'facture_id'    => $facture->id,
                            'produits_id'   => $produitId,
                            'quantite'      => $quantite,
                            'prix_unitaire' => $prixUnitaire,
                            'designation'   => $request->designation[$index] ?? null,
                        ]);
                    }
                }
            }

            // Gestion des règlements multiples (Multi-mode de paiement)
            if ($request->has('reglement_montant') && is_array($request->reglement_montant)) {
                $rModes = (array) $request->input('reglement_mode', []);
                $rMontants = (array) $request->input('reglement_montant', []);
                $rRefs = (array) $request->input('reglement_reference', []);

                foreach ($rMontants as $rIdx => $rMnt) {
                    $rMnt = (float) $rMnt;
                    if ($rMnt > 0) {
                        $mCode = $rModes[$rIdx] ?? 'ESP';
                        $rModeObj = ModePaie::where('code', $mCode)->orWhere('libelle', $mCode)->first();
                        $lastReg = \App\Models\Reglement::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
                        $nextRNum = 1 + $rIdx;
                        if ($lastReg && preg_match('/RGL-(\d+)/', $lastReg->code, $regMatches)) {
                            $nextRNum = intval($regMatches[1]) + 1 + $rIdx;
                        }
                        $rCode = 'RGL-' . str_pad($nextRNum, 5, '0', STR_PAD_LEFT) . '/' . $year;

                        \App\Models\Reglement::create([
                            'code' => $rCode,
                            'date' => $request->date ?? date('Y-m-d'),
                            'mode' => $rModeObj ? $rModeObj->libelle : $mCode,
                            'montant' => $rMnt,
                            'reference_paiement' => $rRefs[$rIdx] ?? null,
                            'facture_id' => $facture->id,
                            'users_id' => auth()->id() ?? $request->user_id,
                        ]);
                    }
                }
            }

            \App\Models\AuditLog::enregistrer('CREATE', 'FACTURE', "Création de la facture de vente {$facture->code}", $facture->id);

            // Certification immédiate si demandée par l'utilisateur ou si signature automatique activée
            $actionType = $request->input('action_type');
            $isAutoSign = (bool) optional(Entete::first())->sfe_automatic_invoice_signing;
            $certifieeDirect = false;

            $errorMessageMcf = null;
            if ($actionType === 'certifier' || $isAutoSign) {
                try {
                    $this->sfeSigner->sign($facture->fresh());
                    $certifieeDirect = true;
                    // Décrémenter le stock physique UNIQUEMENT lorsque la certification fiscale réussit
                    $this->decrementStockFacture($facture);
                } catch (\Throwable $e) {
                    report($e);
                    \Log::warning('Fiscalisation MCF directe échouée à la création', [
                        'facture_id' => $facture->id,
                        'message' => $e->getMessage()
                    ]);
                    $notesArr = !empty($facture->notes) ? (json_decode($facture->notes, true) ?: []) : [];
                    $notesArr['sfe_error'] = $e->getMessage();
                    $facture->update([
                        'statut' => 'Erreur SFE',
                        'notes' => json_encode($notesArr, JSON_UNESCAPED_UNICODE)
                    ]);
                    $errorMessageMcf = $e->getMessage();
                }
            }

            DB::commit();

            // Retourner JSON si c'est une requête AJAX, sinon redirection
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'facture' => [
                        'id' => $facture->id,
                        'code' => $facture->code,
                        'statut' => $facture->fresh()->statut,
                        'is_certified' => $certifieeDirect || $facture->fresh()->isCertified(),
                    ],
                ]);
            }

            if ($certifieeDirect) {
                smilify('success', "Facture {$facture->code} créée et certifiée fiscalement par le boîtier MCF !");
            } elseif (!empty($errorMessageMcf)) {
                smilify('warning', "Facture {$facture->code} enregistrée, mais la certification MCF a échoué : {$errorMessageMcf}");
            } else {
                smilify('success', 'Facture ' . $facture->code . ' créée en brouillon (' . count((array) $request->produits_id) . ' élément(s)) !');
            }

            return redirect()->route('module_ordre_facture.index');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            $message = 'Création de la facture impossible : ' . $e->getMessage();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            smilify('error', $message);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Afficher une facture.
     */
    public function show($id)
    {
        $facture = Facture::with(['Contribuable.Categorie', 'Service', 'Categorie', 'User', 'Typefacture', 'ModePaie', 'Bic', 'Reglements'])->findOrFail($id);
        $elements = ElementFacture::with(['Produit.tva'])->where('facture_id', $facture->id)->get();
        $entete = Entete::singleton();

        $data = $this->calculerSyntheseFacture($facture, $elements, $entete);

        $montant_total = $data['net_a_payer'];
        $contribuables = Contribuable::orderBy('assujeti')->get();
        $bases = Produit::orderBy('code')->get();
        $produits = $bases;

        return view('pages.facture.show', array_merge($data, compact('facture', 'elements', 'montant_total', 'contribuables', 'bases', 'produits', 'entete')));
    }

    /**
     * Retourner les détails d'une facture et ses lignes en format JSON complet (fiscalement balancé).
     */
    public function getJson($id)
    {
        $facture = Facture::with([
            'ElementFactures.Produit.tva',
            'Contribuable',
            'Typefacture',
            'Bic',
            'Reglements',
            'FactureAvoirs'
        ])->findOrFail($id);

        $tauxBic = (float) optional($facture->Bic)->taux;
        $remise1 = (float) ($facture->remise_1 ?? 0);
        $remise2 = (float) ($facture->remise_2 ?? 0);
        $aSubiAvoir = $facture->aSubiAvoir();
        $aAvoirTotal = $facture->aAvoirTotal();
        $canRan = !$aSubiAvoir;
        $soldeAvoirDispo = (float) $facture->soldeAvoirDisponible();

        return response()->json([
            'id' => $facture->id,
            'code' => $facture->code,
            'date' => optional($facture->date)->format('Y-m-d') ?? date('Y-m-d'),
            'contribuable_id' => $facture->contribuable_id,
            'client_nom' => optional($facture->Contribuable)->assujeti,
            'client_ifu' => optional($facture->Contribuable)->ifu,
            'reference' => $facture->reference,
            'taux_bic' => $tauxBic,
            'remise_1' => $remise1,
            'remise_2' => $remise2,
            'montant_total' => (float) $facture->montant_total,
            'montant_paye' => (float) $facture->montant_paye,
            'reste_a_payer' => (float) $facture->reste_a_payer,
            'trop_percu' => (float) $facture->trop_percu,
            'solde_avoir_disponible' => $soldeAvoirDispo,
            'a_subi_avoir' => $aSubiAvoir,
            'a_deja_avoir_partiel' => ($aSubiAvoir && !$aAvoirTotal),
            'can_ran' => $canRan,
            'a_avoir_total' => $aAvoirTotal,
            'lignes' => $facture->ElementFactures->map(function ($el) use ($facture, $tauxBic) {
                $prod = $el->Produit;
                $qteInitiale = (float) $el->quantite;
                $qteDejaAvoir = $facture->quantiteAvoirProduit((int) $el->produits_id);
                $qteRestante = max(0.0, round($qteInitiale - $qteDejaAvoir, 2));

                $qte = $qteRestante;
                $pu = (float) $el->prix_unitaire;
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
                    'id' => $el->id,
                    'produit_id' => $el->produits_id,
                    'designation' => $el->designation ?: optional($prod)->designation,
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
                    'montant_total' => $totalTtcLigne,
                ];
            })->values(),
        ]);
    }

    /**
     * Vue d'impression d'une facture au format officiel A4 DGI.
     */
    public function print($id)
    {
        $facture = Facture::with(['Contribuable.Categorie', 'Service', 'Categorie', 'User', 'Bic', 'ModePaie', 'Reglements', 'FactureAvoirs', 'McfCertifications'])->findOrFail($id);
        $elements = ElementFacture::with(['Produit.tva'])->where('facture_id', $facture->id)->get();
        $entete = Entete::singleton();

        // Gestion de l'archive immuable si certifiée (Piste d'Audit Fiable DGI)
        if ($facture->isCertified()) {
            $duplicata = \App\Models\FactureDuplicata::where('facture_id', $facture->id)->first();
            if (!$duplicata) {
                $this->genererArchiveDuplicata($facture, $elements, $entete);
            }
        }

        // Détection du DUPLICATA : Dès qu'une facture a déjà été imprimée ou explicitement demandée
        $isDuplicata = false;
        if ($facture->statut !== 'BROUILLON') {
            if ($facture->print_count > 0 || request('duplicata') == 1) {
                $isDuplicata = true;
            }
            if (!request()->has('iframe')) {
                $facture->increment('print_count');
                if (!$facture->first_printed_at) {
                    $facture->update(['first_printed_at' => now()]);
                }
            }
        }

        $data = $this->calculerSyntheseFacture($facture, $elements, $entete);
        $data['isDuplicata'] = $isDuplicata;

        return view('pages.facture.print_facture', $data);
    }

    /**
     * Vue d'impression d'une facture au format ticket de caisse POS 80mm.
     */
    public function printTicket($id)
    {
        $facture = Facture::with(['Contribuable.Categorie', 'Service', 'Categorie', 'User', 'Bic', 'ModePaie', 'Reglements', 'FactureAvoirs', 'McfCertifications'])->findOrFail($id);
        $elements = ElementFacture::with(['Produit.tva'])->where('facture_id', $facture->id)->get();
        $entete = Entete::singleton();

        $isDuplicata = false;
        if ($facture->statut !== 'BROUILLON') {
            if ($facture->print_count > 0 || request('duplicata') == 1) {
                $isDuplicata = true;
            }
            if (!request()->has('iframe')) {
                $facture->increment('print_count');
                if (!$facture->first_printed_at) {
                    $facture->update(['first_printed_at' => now()]);
                }
            }
        }

        $data = $this->calculerSyntheseFacture($facture, $elements, $entete);
        $data['isDuplicata'] = $isDuplicata;

        return view('pages.facture.print_ticket', $data);
    }

    /**
     * Enregistrer l'impression effective d'une facture pour déclencher le Duplicata dès les prochaines impressions.
     */
    public function marquerImprimee($id)
    {
        $facture = Facture::findOrFail($id);
        $facture->increment('print_count');
        if (!$facture->first_printed_at) {
            $facture->update(['first_printed_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'print_count' => $facture->print_count,
            'is_duplicata' => $facture->print_count > 1,
        ]);
    }

    /**
     * Crée l'archive immuable au moment de la certification (Règle Piste d'audit DGI).
     */
    protected function genererArchiveDuplicata(Facture $facture, $elements, Entete $entete): \App\Models\FactureDuplicata
    {
        $vendeurSnapshot = [
            'nom'               => $entete->denomination,
            'ifu'               => $entete->ifu,
            'rccm'              => $entete->rccm,
            'telephone'         => $entete->telephone,
            'email'             => $entete->email,
            'regime_fiscal'     => $entete->regime_fiscal,
            'division_fiscal'   => $entete->division_fiscal,
            'comptes_bancaires' => $entete->comptes_bancaires,
        ];

        $clientSnapshot = [
            'nom'           => optional($facture->Contribuable)->assujeti ?? 'Client Comptoir',
            'ifu'           => optional($facture->Contribuable)->ifu ?? '',
            'rccm'          => optional($facture->Contribuable)->rccm ?? '',
            'telephone'     => optional($facture->Contribuable)->telephone ?? '',
            'regime_fiscal' => optional($facture->Contribuable)->regime_fiscal ?? '',
            'categorie'     => optional(optional($facture->Contribuable)->Categorie)->libelle ?? '',
        ];

        $lignesSnapshot = [];
        foreach ($elements as $el) {
            $lignesSnapshot[] = [
                'designation'    => $el->designation ?: optional($el->Produit)->designation,
                'label_fiscal'   => optional($el->Produit)->label_fiscal,
                'quantite'       => (float) $el->quantite,
                'prix_unitaire'  => (float) $el->prix_unitaire,
                'taux_tva'       => (float) optional(optional($el->Produit)->tva)->taux,
                'type_ts'        => optional($el->Produit)->type_ts,
                'montant_ts'     => (float) optional($el->Produit)->montant_ts,
                'pourcentage_ts' => (float) optional($el->Produit)->pourcentage_ts,
            ];
        }

        $certif = $facture->certification_active;

        return \App\Models\FactureDuplicata::firstOrCreate(
            ['facture_id' => $facture->id],
            [
                'numero_interne'     => $facture->code,
                'date_certification' => $certif?->date_certification ?? now(),
                'vendeur_json'       => $vendeurSnapshot,
                'client_json'        => $clientSnapshot,
                'lignes_json'        => $lignesSnapshot,
                'paiements_json'     => $facture->Reglements->toArray(),
                'totaux_json'        => [
                    'montant_total' => $facture->montant_total,
                ],
                'dgi_json'           => [
                    'signature' => $facture->dgi_signature,
                    'nim'       => $facture->dgi_nim_machine,
                    'compteur'  => $facture->dgi_compteur,
                    'qrcode'    => $facture->dgi_qrcode,
                ],
            ]
        );
    }

    /**
     * Calcule la synthèse fiscale stricte conforme aux normes DGI / SECeF BF ST 1.0.
     */
    protected function calculerSyntheseFacture(Facture $facture, $elements, Entete $entete): array
    {
        $total_ht_brut = 0.0;
        $total_ts = 0.0;
        $montant_transport = 0.0;
        $transport_label = 'Frais de transport';

        // Lignes ventilées par groupe de taxation réel (A, B, C...)
        $lignesParGroupe = [];

        foreach ($elements as $element) {
            $code = strtoupper((string) optional($element->Produit)->code);
            if ($code === 'TRANS') {
                $montant_transport += (float) $element->quantite * (float) $element->prix_unitaire;
                $transport_label = $element->designation ?: 'Frais de transport';
                continue;
            }

            $qte = (float) $element->quantite;
            $pu = (float) $element->prix_unitaire;
            $montantLigneHt = $qte * $pu;
            $total_ht_brut += $montantLigneHt;

            $produit = $element->Produit;
            $tsUnit = 0.0;
            if ($produit) {
                if ($produit->type_ts === 'POURCENTAGE' && (float) ($produit->pourcentage_ts ?? 0) > 0) {
                    $tsUnit = round($pu * ((float) $produit->pourcentage_ts / 100.0), 2);
                } else {
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

        // Remises globales réparties au prorata du HT
        $remise_1 = (float) ($facture->remise_1 ?? 0);
        $remise_2 = (float) ($facture->remise_2 ?? 0);
        $remise_totale = $remise_1 + $remise_2;
        $net_commercial = max(0, $total_ht_brut - $remise_totale);
        $prorata = $total_ht_brut > 0 ? ($net_commercial / $total_ht_brut) : 1.0;

        // Calcul exact de chaque groupe de TVA
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
                'base' => $baseHt, // Base HT pure du groupe conforme au Total Net HT
                'base_tva' => $baseTva, // Base taxable TVA (HT + TS)
                'taxe' => $montantTva,
                'ts' => $info['ts'],
            ];
        }

        $total_ttc_brut = $net_commercial + $total_ts + $total_taxe;

        // PSVB (appliqué UNIQUEMENT sur les biens matériels LOCBIE / IMPBIE, jamais sur les services LOCSER / IMPSER)
        $tauxBic = (float) optional($facture->Bic)->taux;
        $total_bic = 0.0;
        $bic_groups = [];
        if ($tauxBic > 0) {
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

        // Timbre fiscal de quittance (en cas de paiement en espèces selon le barème DGI)
        $total_timbre = 0.0;
        $isEspeces = optional($facture->ModePaie)->code === 'ESP';
        if ($isEspeces && !in_array($facture->type_code, ['FA', 'EA'], true)) {
            $ttcBase = $total_ttc_brut + $total_bic;
            if ($ttcBase >= 1000 && $ttcBase <= 5000) {
                $total_timbre = 30.0;
            } elseif ($ttcBase > 5000 && $ttcBase <= 10000) {
                $total_timbre = 50.0;
            } elseif ($ttcBase > 10000 && $ttcBase <= 50000) {
                $total_timbre = 100.0;
            } elseif ($ttcBase > 50000) {
                $total_timbre = 100.0 + ceil(($ttcBase - 50000) / 50000) * 50.0;
            }
        }

        // Le Net à Payer officiel de la facture est son montant total TTC
        $total_ttc = round($total_ttc_brut + $total_bic + $montant_transport, 2);
        $net_a_payer = $total_ttc;
        $avance = (float) $facture->Reglements()->sum('montant');
        $reste_a_payer = max(0, round($net_a_payer - $avance, 2));

        $typeCode = $facture->type_code;
        $isExport = in_array($typeCode, ['EV', 'ET'], true);
        $isAcompte = in_array($typeCode, ['FT', 'ET'], true);
        $isAvoir = in_array($typeCode, ['FA', 'EA'], true);
        $isCertified = (bool) ($facture->certification_active || $facture->dgi_signature);
        $notesArray = $facture->commentaires_dgi;
        $modePrix = strtoupper((string) ($notesArray['mode_prix'] ?? ($total_taxe > 0 ? 'TTC' : 'HT')));

        return [
            'facture' => $facture,
            'elements' => $elements,
            'entete' => $entete,
            'total_ht' => $total_ht_brut,
            'remise_1' => $remise_1,
            'remise_2' => $remise_2,
            'remise_totale' => $remise_totale,
            'net_commercial' => $net_commercial,
            'tva_groups' => $tva_groups,
            'total_taxe' => $total_taxe,
            'total_ts' => $total_ts,
            'bic_groups' => $bic_groups,
            'total_bic' => $total_bic,
            'total_timbre' => $total_timbre,
            'montant_transport' => $montant_transport,
            'transport_label' => $transport_label,
            'total_ttc' => $total_ttc,
            'avance' => $avance,
            'reste_a_payer' => $reste_a_payer,
            'net_a_payer' => $net_a_payer,
            'typeCode' => $typeCode,
            'isExport' => $isExport,
            'isAcompte' => $isAcompte,
            'isAvoir' => $isAvoir,
            'isCertified' => $isCertified,
            'mode_prix' => $modePrix,
        ];
    }

    /**
     * Vue d'impression du bon de livraison associé à une facture.
     */
    public function bon_livraison($id)
    {
        $facture = Facture::with(['Contribuable.Categorie', 'Service', 'Categorie', 'User'])->findOrFail($id);
        $elements = ElementFacture::with('Produit.tva')->where('facture_id', $facture->id)->get();
        $entete = Entete::first();

        // Totaux
        $total_ht = 0;
        $tva_groups = [];

        foreach ($elements as $element) {
            $quantite = (float) $element->quantite;
            $prix     = (float) $element->prix_unitaire;

            $montantHt  = $quantite * $prix;
            $taux       = (float) optional(optional($element->Produit)->tva)->taux;
            $taxe       = $montantHt * $taux / 100;

            $total_ht     += $montantHt;

            $key = (string) $taux;
            if (!isset($tva_groups[$key])) {
                $tva_groups[$key] = ['taux' => $taux, 'base' => 0, 'taxe' => 0];
            }
            $tva_groups[$key]['base'] += $montantHt;
            $tva_groups[$key]['taxe'] += $taxe;
        }

        $total_taxe  = array_sum(array_column($tva_groups, 'taxe'));
        $total_ttc   = $total_ht + $total_taxe;
        $net_a_payer = $total_ttc;

        return view('pages.facture.bon_livraison', compact(
            'facture', 'elements', 'entete',
            'total_ht', 'tva_groups',
            'total_taxe', 'total_ttc', 'net_a_payer'
        ));
    }

    /**
     * Formulaire d'édition d'une facture.
     */
    public function edit($id)
    {
        $facture = Facture::with(['Contribuable', 'Service', 'Categorie'])->findOrFail($id);
        if ($facture->is_fiscal_locked) {
            abort(423, 'Facture fiscalisée : modification interdite. Utilisez un avoir fiscal.');
        }
        $elements = ElementFacture::with('Produit')->where('facture_id', $facture->id)->get();
        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();
        $typefactures = Typefacture::whereIn('code', ['FV', 'FT', 'EV', 'ET'])->orderBy('code')->get();
        $modepaies = ModePaie::orderBy('code')->get();
        $bics = Bic::orderBy('code')->get();

        return view('pages.facture.edit', compact('facture', 'elements', 'contribuables', 'produits', 'typefactures', 'modepaies', 'bics'));
    }

    /**
     * Mettre à jour la facture + ses éléments.
     */
    public function update(Request $request, $id)
    {
        $facture = Facture::findOrFail($id);

        if ($facture->is_fiscal_locked) {
            abort(423, 'Facture fiscalisée : toute modification est interdite. Émettez un avoir fiscal.');
        }

        DB::transaction(function () use ($request, $facture) {
            $facture->update([
                'code'             => $request->code ?? $facture->code,
                'reference'        => $request->reference,
                'date'             => $request->date,
                'statut'           => $request->statut ?? $facture->statut,
                'contribuable_id' => $request->contribuable_id ?? $facture->contribuable_id,
                'service_id'       => $request->service_id ?? $facture->service_id,
                'modepaie_id'    => $request->modepaie_id ?? $facture->modepaie_id,
                'typefacture_id' => $request->typefacture_id ?? $facture->typefacture_id,
                'bic_id'         => $request->bic_id ?? $facture->bic_id,
                'remise_1'       => $request->remise_1 ?? $facture->remise_1,
                'remise_2'       => $request->remise_2 ?? $facture->remise_2,
            ]);

            $this->syncElements($facture, $request);
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture mise à jour avec succès.');
        }

        return redirect()->route('module_ordre_facture.show', $facture->id);
    }

    /**
     * Supprime une facture non certifiée (Brouillon ou Erreur MCF).
     * Restitue les stocks, déverrouille le devis/proforma et nettoie la base de données.
     */
    public function destroy($id)
    {
        $facture = Facture::with(['ElementFactures.Produit', 'Reglements'])->findOrFail($id);

        if ($facture->isCertified()) {
            if (function_exists('smilify')) {
                smilify('error', "Suppression interdite par la loi fiscale DGI : la facture {$facture->code} est certifiée. Émettez un avoir d'annulation totale (RAN).");
            }
            return redirect()->route('module_ordre_facture.index');
        }

        DB::transaction(function () use ($facture, $id) {
            // 1. Restituer le stock si décrémenté
            foreach ($facture->ElementFactures as $element) {
                $produit = $element->Produit;
                if ($produit && $produit->isBien() && (float) $element->quantite > 0) {
                    $produit->increment('qte', (float) $element->quantite);
                }
            }

            // 2. Déverrouiller le devis / proforma d'origine s'il y a lieu
            if ($facture->facture_proforma_id) {
                \App\Models\FactureProforma::where('id', $facture->facture_proforma_id)->update(['statut' => 'validée']);
            }

            // 3. Supprimer règlements brouillons, éléments et la facture
            $code = $facture->code;
            $facture->Reglements()->delete();
            $facture->ElementFactures()->delete();
            $facture->delete();

            \App\Models\AuditLog::enregistrer('DELETE', 'FACTURE', "Suppression de la facture non certifiée {$code} et restitution des stocks.", $id);
        });

        if (function_exists('smilify')) {
            smilify('success', "Facture non certifiée {$facture->code} supprimée avec succès et stocks réajustés.");
        }
        return redirect()->route('module_ordre_facture.index');
    }


    /**
     * Création des éléments depuis un payload "tableau" du formulaire.
     */
    protected function saveElements(Facture $facture, Request $request): void
    {
        $produits     = (array) $request->input('produits_id', []);
        $designations = (array) $request->input('designation', []);
        $quantites    = (array) $request->input('quantite', []);
        $prix         = (array) $request->input('prix_unitaire', []);

        $count = max(count($produits), count($quantites), count($prix));

        for ($i = 0; $i < $count; $i++) {
            $produitId = $produits[$i] ?? null;
            $quantite  = isset($quantites[$i]) ? (float) $quantites[$i] : 0;
            $prixU     = isset($prix[$i]) ? (float) $prix[$i] : 0;

            if (!$produitId && $quantite == 0 && $prixU == 0) {
                continue;
            }

            $montant = $quantite * $prixU;

            ElementFacture::create([
                'facture_id'    => $facture->id,
                'produits_id'   => $produitId,
                'designation'   => $designations[$i] ?? null,
                'quantite'      => $quantite,
                'prix_unitaire' => $prixU,
                'montant_total' => $montant,
            ]);
        }
    }

    /**
     * Synchroniser les éléments en édition (upsert via element_id[]).
     */
    protected function syncElements(Facture $facture, Request $request): void
    {
        $elementIds   = (array) $request->input('element_id', []);
        $produits     = (array) $request->input('produits_id', []);
        $designations = (array) $request->input('designation', []);
        $quantites    = (array) $request->input('quantite', []);
        $prix         = (array) $request->input('prix_unitaire', []);

        $keptIds = [];
        $count = max(count($produits), count($quantites), count($prix));

        for ($i = 0; $i < $count; $i++) {
            $produitId = $produits[$i] ?? null;
            $quantite  = isset($quantites[$i]) ? (float) $quantites[$i] : 0;
            $prixU     = isset($prix[$i]) ? (float) $prix[$i] : 0;

            if (!$produitId && $quantite == 0 && $prixU == 0) {
                continue;
            }

            $montant = $quantite * $prixU;

            $data = [
                'facture_id'    => $facture->id,
                'produits_id'   => $produitId,
                'designation'   => $designations[$i] ?? null,
                'quantite'      => $quantite,
                'prix_unitaire' => $prixU,
                'montant_total' => $montant,
            ];

            $existingId = $elementIds[$i] ?? null;
            if ($existingId) {
                $element = ElementFacture::find($existingId);
                if ($element) {
                    $element->update($data);
                    $keptIds[] = $element->id;
                    continue;
                }
            }

            $element = ElementFacture::create($data);
            $keptIds[] = $element->id;
        }

        // Supprimer les anciens éléments qui n'ont pas été renvoyés
        ElementFacture::where('facture_id', $facture->id)
            ->whereNotIn('id', $keptIds)
            ->delete();
    }

    /**
     * Tente de recertifier une facture spécifique en statut Brouillon ou Erreur MCF.
     */
    public function recertifier($id)
    {
        $facture = Facture::findOrFail($id);

        if ($facture->isCertified()) {
            smilify('info', "La facture {$facture->code} est déjà certifiée fiscalement.");
            return redirect()->route('module_ordre_facture.index');
        }

        try {
            $this->sfeSigner->sign($facture);
            $this->decrementStockFacture($facture);
            \App\Models\AuditLog::enregistrer('SIGN', 'FACTURE', "Certification réussie de la facture {$facture->code} et décrémentation des stocks", $facture->id);
            smilify('success', "Facture {$facture->code} certifiée avec succès par le boîtier MCF Eltrade (SECeF ST 1.0) !");
        } catch (\Throwable $e) {
            report($e);
            $facture->update(['statut' => 'Erreur MCF']);
            \App\Models\AuditLog::enregistrer('ERROR', 'MCF', "Échec de certification facture {$facture->code} : " . $e->getMessage(), $facture->id);
            smilify('error', "Échec de certification MCF : " . $e->getMessage());
        }

        return redirect()->route('module_ordre_facture.index');
    }

    /**
     * Tente de recertifier en lot toutes les factures en erreur.
     */
    public function recertifierTout()
    {
        $factures = Facture::where('statut', 'Erreur MCF')->get();
        if ($factures->isEmpty()) {
            smilify('info', "Aucune facture en erreur à recertifier.");
            return redirect()->route('module_ordre_facture.index');
        }

        $succes = 0;
        $echecs = 0;

        foreach ($factures as $facture) {
            try {
                $this->sfeSigner->sign($facture);
                $this->decrementStockFacture($facture);
                $succes++;
                usleep(500000); // Pause de 500ms entre envois série
            } catch (\Throwable $e) {
                $echecs++;
            }
        }

        if ($succes > 0 && $echecs === 0) {
            smilify('success', "{$succes} facture(s) recertifiée(s) avec succès !");
        } elseif ($succes > 0 && $echecs > 0) {
            smilify('warning', "{$succes} facture(s) recertifiée(s) avec succès, {$echecs} encore en erreur.");
        } else {
            smilify('error', "Échec de recertification sur les {$echecs} facture(s). Vérifiez la liaison avec le boîtier MCF.");
        }

        return redirect()->route('module_ordre_facture.index');
    }

    /**
     * Décrémente le stock global UNIQUEMENT pour les biens physiques vendus.
     */
    protected function decrementStockFacture(Facture $facture): void
    {
        $facture->loadMissing('ElementFactures.Produit');
        foreach ($facture->ElementFactures as $element) {
            $produit = $element->Produit;
            if ($produit && $produit->isBien() && (float) $element->quantite > 0) {
                $produit->decrement('qte', (float) $element->quantite);
            }
        }
    }

    /**
     * Annule la tentative de certification d'une facture en erreur MCF / SFE.
     * Restitue immédiatement les stocks décrémentés, supprime les règlements en attente,
     * et remet la facture en statut Brouillon pour correction ou suppression.
     */
    public function annulerErreur($id)
    {
        $facture = Facture::with(['ElementFactures.Produit', 'Reglements'])->findOrFail($id);

        if ($facture->isCertified()) {
            smilify('error', "La facture {$facture->code} est déjà certifiée fiscalement. Conformément à la norme DGI, une facture certifiée ne peut pas être annulée directement ; elle doit faire l'objet d'un avoir fiscal.");
            return redirect()->route('module_ordre_facture.index');
        }

        DB::transaction(function () use ($facture) {
            // 1. Restituer le stock physique des biens décrémentés
            foreach ($facture->ElementFactures as $element) {
                $produit = $element->Produit;
                if ($produit && $produit->isBien() && (float) $element->quantite > 0) {
                    $produit->increment('qte', (float) $element->quantite);
                }
            }

            // 2. Supprimer les règlements attachés à cette facture non certifiée
            $facture->Reglements()->delete();

            // 3. Réinitialiser la facture en statut Brouillon et nettoyer les erreurs
            $notes = $facture->notes;
            if (!empty($notes) && str_starts_with(trim($notes), '{')) {
                $notesArr = json_decode($notes, true) ?: [];
                unset($notesArr['sfe_error']);
                $notes = json_encode($notesArr, JSON_UNESCAPED_UNICODE);
            }

            $facture->update([
                'statut' => 'Brouillon',
                'notes' => $notes,
            ]);

            \App\Models\AuditLog::enregistrer('UPDATE', 'FACTURE', "Annulation tentative certification facture {$facture->code} : stocks restitués et remise en Brouillon", $facture->id);
        });


        if (function_exists('smilify')) {
            smilify('success', "Facture {$facture->code} réinitialisée en Brouillon avec succès. Les stocks ont été réintégrés et les règlements en attente ont été annulés. Vous pouvez désormais la modifier ou la supprimer.");
        }
        return redirect()->route('module_ordre_facture.index');
    }
}

