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
        $collection = Facture::with(['Contribuable', 'Service', 'Categorie'])
            ->orderByDesc('id')
            ->get();

        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();
        $produitsData = $produits->keyBy('id')->map(function ($produit) {
            return [
                'code' => $produit->code,
                'designation' => $produit->designation,
                'prix_unitaire' => $produit->prix_unitaire,
                'qte' => $produit->qte,
            ];
        });
        // FA/EA are certified via FactureAvoir, not the sales form.
        $typefactures = Typefacture::whereIn('code', ['FV', 'FT', 'EV', 'ET'])->orderBy('code')->get();
        $modepaies = ModePaie::orderBy('code')->get();
        $bics = Bic::orderBy('code')->get();
        $categories = \App\Models\Categorie::orderBy('code')->get();
        $tvas = \App\Models\Tva::orderBy('code')->get();

        return view('pages.facture.index', compact(
            'collection',
            'contribuables',
            'produits',
            'produitsData',
            'typefactures',
            'modepaies',
            'bics',
            'categories',
            'tvas'
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
            if ($produit && (float) ($produit->qte ?? 0) < $qteDemandee) {
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

            $nextNumber = ($lastFacture ? intval(substr($lastFacture->code, 4, 5)) : 0) + 1;
            $generatedCode = 'FAC-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            $facture = Facture::create([
                'reference'        => $request->reference,
                'date'             => $request->date,
                'code'             => $generatedCode,
                'contribuable_id' => $request->contribuable_id,
                'service_id'      => $request->service_id,
                'user_id'        => $request->user_id,
                'typefacture_id' => $request->typefacture_id ?? null,
                'modepaie_id'    => $request->modepaie_id ?? null,
                'bic_id'         => $request->bic_id ?? null,
                'remise_1'        => $request->remise_1 ?? 0,
                'remise_2'        => $request->remise_2 ?? 0,
            ]);

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

                        // Décrémenter le stock global du produit (vente client)
                        if ($quantite > 0) {
                            Produit::where('id', $produitId)->decrement('qte', $quantite);
                        }
                    }
                }
            }

            if ((bool) optional(Entete::first())->sfe_automatic_invoice_signing) {
                $this->sfeSigner->sign($facture->fresh());
            }

            DB::commit();

            // Retourner JSON si c'est une requête AJAX, sinon redirection
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'facture' => [
                        'id' => $facture->id,
                        'code' => $facture->code,
                        'statut' => $facture->statut,
                    ],
                ]);
            }

            smilify('success', 'Facture créée en brouillon (' . count((array) $request->produits_id) . ' élément(s)) !');
            return redirect("module_ordre_facture/" . $facture->id);

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
        $facture = Facture::with(['Contribuable', 'Service', 'Categorie', 'User', 'Typefacture', 'ModePaie', 'Bic'])->findOrFail($id);
        $elements = ElementFacture::with(['Produit.tva'])->where('facture_id', $facture->id)->get();

        $montant_total = $elements->sum(function ($element) {
            return $element->montant_total ?? ($element->quantite * $element->prix_unitaire);
        });

        $contribuables = Contribuable::orderBy('assujeti')->get();
        $bases = Produit::orderBy('code')->get();
        $produits = $bases;

        return view('pages.facture.show', compact('facture', 'elements', 'montant_total', 'contribuables', 'bases', 'produits'));
    }

    /**
     * Vue d'impression d'une facture.
     */
    public function print($id)
    {
        $facture = Facture::with(['Contribuable.Categorie', 'Service', 'Categorie', 'User', 'Bic'])->findOrFail($id);
        $elements = ElementFacture::with(['Produit.tva'])->where('facture_id', $facture->id)->get();
        $entete = Entete::first();

        // 1) Total brut HT + recensement des taux uniques (TVA et BIC)
        //    Le produit avec le code "TRANS" est exclu des totaux (transport non taxable)
        //    mais son montant est conservé à part et réinjecté dans le Net à payer.
        $total_ht          = 0.0;
        $montant_transport = 0.0;
        $transport_qte     = 0.0;
        $transport_prix    = 0.0;
        $transport_label   = 'Frais de Transport';
        $tva_groups = []; // [taux => ['taux','base','taxe']]
        $bic_groups = []; // [taux => ['taux','base_ttc','montant']]

        foreach ($elements as $element) {
            $code = strtoupper((string) optional($element->Produit)->code);
            if ($code === 'TRANS') {
                $qte = (float) $element->quantite;
                $pu  = (float) $element->prix_unitaire;
                $montant_transport += $qte * $pu;
                $transport_qte  += $qte;
                $transport_prix  = $pu; // dernier prix unitaire rencontré
                if ($desig = $element->designation ?? optional($element->Produit)->designation) {
                    $transport_label = $desig;
                }
                continue;
            }

            $quantite  = (float) $element->quantite;
            $prix      = (float) $element->prix_unitaire;
            $montantHt = $quantite * $prix;

            $total_ht += $montantHt;

            $tauxTva = (float) optional(optional($element->Produit)->tva)->taux;
            $keyTva  = (string) $tauxTva;
            if (!isset($tva_groups[$keyTva])) {
                $tva_groups[$keyTva] = ['taux' => $tauxTva, 'base' => 0.0, 'taxe' => 0.0];
            }
        }

        // Récupérer le taux BIC de la facture (une seule fois)
        $tauxBic = (float) optional($facture->Bic)->taux;
        if ($tauxBic > 0) {
            $keyBic = (string) $tauxBic;
            $bic_groups[$keyBic] = ['taux' => $tauxBic, 'base_ttc' => 0.0, 'montant' => 0.0];
        }

        // 2) Net commercial = Total brut HT − Remise
        $remise_1         = (float) ($facture->remise_1 ?? 0);
        $remise_2         = (float) ($facture->remise_2 ?? 0);
        $net_commercial = max(0, $total_ht - $remise_1 - $remise_2);

        // 3) TVA : chaque taux présent est appliqué sur la TOTALITÉ du Net commercial
        //    TVA(taux) = Net commercial × taux
        foreach ($tva_groups as $key => $g) {
            $tva_groups[$key]['base'] = $net_commercial;
            $tva_groups[$key]['taxe'] = $net_commercial * $g['taux'] / 100;
        }
        $total_taxe = array_sum(array_column($tva_groups, 'taxe'));

        // 4) Total TTC = Net commercial + somme des TVA
        $total_ttc = $net_commercial + $total_taxe;

        // 5) BIC : chaque taux présent est appliqué sur la TOTALITÉ du Total TTC
        //    BIC(taux) = Total TTC × taux
        foreach ($bic_groups as $key => $g) {
            $bic_groups[$key]['base_ttc'] = $total_ttc;
            $bic_groups[$key]['montant']  = $total_ttc * $g['taux'] / 100;
        }
        $total_bic = array_sum(array_column($bic_groups, 'montant'));

        // 6) Net à payer = Total TTC + BIC + Transport (l'avance est déduite côté vue)
        $net_a_payer = $total_ttc + $total_bic + $montant_transport;

        $typeCode = $facture->type_code;
        $isExport = in_array($typeCode, ['EV', 'ET'], true);
        $isAcompte = in_array($typeCode, ['FT', 'ET'], true);
        $requireCertForPrint = (bool) optional($entete)->sfe_automatic_invoice_signing;
        $isCertified = (bool) ($facture->certification_active || $facture->dgi_signature);

        return view('pages.facture.print_facture', compact(
            'facture', 'elements', 'entete',
            'total_ht', 'remise_1', 'remise_2', 'net_commercial',
            'tva_groups', 'total_taxe',
            'bic_groups', 'total_bic',
            'montant_transport', 'transport_label',
            'transport_qte', 'transport_prix',
            'total_ttc', 'net_a_payer',
            'typeCode', 'isExport', 'isAcompte',
            'requireCertForPrint', 'isCertified'
        ));
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
     * Supprimer une facture (avec ses éléments).
     */
    public function destroy($id)
    {
        $facture = Facture::findOrFail($id);

        if ($facture->is_fiscal_locked) {
            smilify('error', 'Facture fiscalisée : suppression interdite. Utilisez un avoir fiscal.');
            return redirect()->route('module_ordre_facture.show', $facture->id);
        }

        // Une facture déjà réglée (au moins un règlement) ne peut pas être supprimée.
        if ($facture->Reglements()->count() > 0) {
            if (function_exists('smilify')) {
                smilify('error', 'Impossible de supprimer une facture réglée.');
            }
            return redirect()->route('module_ordre_facture.show', $facture->id);
        }

        // Rétablir le stock global avant suppression
        $elements = ElementFacture::where('facture_id', $facture->id)->get();
        foreach ($elements as $el) {
            if ($el->produits_id && (float) $el->quantite > 0) {
                Produit::where('id', $el->produits_id)->increment('qte', (float) $el->quantite);
            }
        }

        ElementFacture::where('facture_id', $facture->id)->delete();
        $facture->delete();

        if (function_exists('smilify')) {
            smilify('success', 'Facture supprimée avec succès.');
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
}
