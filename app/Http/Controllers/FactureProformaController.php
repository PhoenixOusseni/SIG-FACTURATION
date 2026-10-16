<?php

namespace App\Http\Controllers;

use App\Models\FactureProforma;
use App\Models\ElementFactureProforma;
use App\Models\Contribuable;
use App\Models\Produit;
use App\Models\Entete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureProformaController extends Controller
{
    /**
     * Liste des factures proforma de l'utilisateur connecté.
     */
    public function index()
    {
        $collection = FactureProforma::with(['Contribuable', 'Service', 'Categorie'])
            ->where('users_id', auth()->id())
            ->orderByDesc('id')
            ->get();

        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();
        $categories = \App\Models\Categorie::orderBy('code')->get();
        $tvas = \App\Models\Tva::orderBy('code')->get();
        $bics = \App\Models\Bic::orderBy('code')->get();

        return view('pages.facture_proforma.index', compact(
            'collection',
            'contribuables',
            'produits',
            'categories',
            'tvas',
            'bics'
        ));
    }

    /**
     * Liste de toutes les factures proforma (vue "liste.blade.php").
     */
    public function all_facture()
    {
        $collection = FactureProforma::with(['Contribuable', 'Service', 'Categorie'])
            ->orderByDesc('id')
            ->get();

        return view('pages.facture_proforma.liste', compact('collection'));
    }

    /**
     * Enregistrer une nouvelle facture proforma + ses éléments.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contribuables_id' => 'required|exists:contribuables,id',
        ]);

        // Avertissement stock (sans bloquer — la proforma ne décrémente pas le stock)
        $produitIds    = array_filter((array) $request->input('produits_id', []));
        $quantites     = (array) $request->input('quantite', []);
        $stockWarnings = [];

        foreach ($produitIds as $i => $produitId) {
            $qte     = (float) ($quantites[$i] ?? 0);
            $produit = Produit::find($produitId);
            if ($produit && (float) ($produit->qte ?? 0) < $qte) {
                $dispo = number_format((float) ($produit->qte ?? 0), 2, ',', ' ');
                $stockWarnings[] = "« {$produit->designation} » — dispo : {$dispo} | demandé : {$qte}";
            }
        }

        if (!empty($stockWarnings)) {
            session()->flash('warning_stock', $stockWarnings);
        }

        DB::transaction(function () use ($request) {
            // Générer automatiquement le code de la facture proforma (PRO-00001/2026)
            $year = date('Y');
            $lastFacture = FactureProforma::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = ($lastFacture ? intval(substr($lastFacture->code, 4, 5)) : 0) + 1;
            $generatedCode = 'PRO-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            $facture = FactureProforma::create([
                'code'             => $generatedCode,
                'reference'        => $request->reference,
                'date'             => $request->date,
                'statut'           => $request->statut ?? 'en attente',
                'users_id'         => $request->users_id ?? auth()->id(),
                'contribuables_id' => $request->contribuables_id,
                'objet'            => $request->objet,
                'remise_1'         => $request->remise_1 ?? 0,
                'remise_2'         => $request->remise_2 ?? 0,
            ]);

            $this->saveElements($facture, $request);
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture proforma enregistrée avec succès.');
        }

        return redirect()->route('module_facture_proforma.index');
    }

    /**
     * Afficher une facture proforma.
     */
    public function show($id)
    {
        $facture = FactureProforma::with(['Contribuable', 'Service', 'Categorie', 'User'])->findOrFail($id);
        $elements = ElementFactureProforma::with(['Produit.tva'])
            ->where('facture_proforma_id', $facture->id)
            ->get();

        $montant_total = $elements->sum(function ($element) {
            return $element->montant_total ?? ($element->quantite * $element->prix_unitaire);
        });

        $contribuables = Contribuable::orderBy('assujeti')->get();
        $bases = Produit::orderBy('code')->get();
        $produits = $bases;

        return view('pages.facture_proforma.show', compact('facture', 'elements', 'montant_total', 'contribuables', 'bases', 'produits'));
    }

    /**
     * Afficher le formulaire d'édition d'une facture proforma.
     */
    public function edit($id)
    {
        $facture = FactureProforma::with(['Contribuable', 'Service'])->findOrFail($id);
        $elements = ElementFactureProforma::with('Produit')
            ->where('facture_proforma_id', $facture->id)
            ->get();
        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();

        return view('pages.facture_proforma.edit', compact('facture', 'elements', 'contribuables', 'produits'));
    }

    /**
     * Mettre à jour la facture proforma + ses éléments.
     */
    public function update(Request $request, $id)
    {
        $facture = FactureProforma::findOrFail($id);

        DB::transaction(function () use ($request, $facture) {
            $facture->update([
                'code'             => $request->code ?? $facture->code,
                'reference'        => $request->reference,
                'date'             => $request->date,
                'statut'           => $request->statut ?? $facture->statut,
                'users_id'         => $request->users_id ?? $facture->users_id,
                'contribuables_id' => $request->contribuables_id ?? $facture->contribuables_id,
                'service_id'       => $request->service_id ?? $facture->service_id,
                'objet'            => $request->objet ?? $facture->objet,
                'remise_1'         => $request->remise_1 ?? $facture->remise_1,
                'remise_2'         => $request->remise_2 ?? $facture->remise_2,
            ]);

            $this->syncElements($facture, $request);
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture proforma mise à jour avec succès.');
        }

        return redirect()->route('module_facture_proforma.show', $facture->id);
    }

    /**
     * Supprimer une facture proforma (avec ses éléments).
     */
    public function destroy($id)
    {
        $facture = FactureProforma::findOrFail($id);
        ElementFactureProforma::where('facture_proforma_id', $facture->id)->delete();
        $facture->delete();

        if (function_exists('smilify')) {
            smilify('success', 'Facture proforma supprimée avec succès.');
        }

        return redirect()->route('module_facture_proforma.index');
    }

    /**
     * Création des éléments depuis un payload "tableau" du formulaire.
     */
    protected function saveElements(FactureProforma $facture, Request $request): void
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

            ElementFactureProforma::create([
                'facture_proforma_id' => $facture->id,
                'produits_id'         => $produitId,
                'designation'         => $designations[$i] ?? null,
                'quantite'            => $quantite,
                'prix_unitaire'       => $prixU,
                'montant_total'       => $montant,
            ]);
        }
    }

    /**
     * Synchroniser les éléments en édition (upsert via element_id[]).
     */
    protected function syncElements(FactureProforma $facture, Request $request): void
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
                'facture_proforma_id' => $facture->id,
                'produits_id'         => $produitId,
                'designation'         => $designations[$i] ?? null,
                'quantite'            => $quantite,
                'prix_unitaire'       => $prixU,
                'montant_total'       => $montant,
            ];

            $existingId = $elementIds[$i] ?? null;
            if ($existingId) {
                $element = ElementFactureProforma::find($existingId);
                if ($element) {
                    $element->update($data);
                    $keptIds[] = $element->id;
                    continue;
                }
            }

            $element = ElementFactureProforma::create($data);
            $keptIds[] = $element->id;
        }

        ElementFactureProforma::where('facture_proforma_id', $facture->id)
            ->whereNotIn('id', $keptIds)
            ->delete();
    }

    /**
     * Vue d'impression d'une facture proforma.
     */
    public function print($id)
    {
        $facture = FactureProforma::with(['Contribuable.Categorie', 'Service', 'Categorie', 'User'])->findOrFail($id);
        $elements = ElementFactureProforma::with(['Produit.tva'])->where('facture_proforma_id', $facture->id)->get();
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
                $transport_prix  = $pu;
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

        // Note: Le BIC n'est pas calculé pour les factures proforma
        // Si nécessaire, ajouter une relation Bic à FactureProforma

        // 2) Net commercial = Total brut HT − Remise 1 − Remise 2
        $remise_1       = (float) ($facture->remise_1 ?? 0);
        $remise_2       = (float) ($facture->remise_2 ?? 0);
        $net_commercial = max(0, $total_ht - $remise_1 - $remise_2);

        // 3) TVA(taux) = Net commercial × taux
        foreach ($tva_groups as $key => $g) {
            $tva_groups[$key]['base'] = $net_commercial;
            $tva_groups[$key]['taxe'] = $net_commercial * $g['taux'] / 100;
        }
        $total_taxe = array_sum(array_column($tva_groups, 'taxe'));

        // 4) Total TTC = Net commercial + Σ TVA
        $total_ttc = $net_commercial + $total_taxe;

        // 5) BIC(taux) = Total TTC × taux
        foreach ($bic_groups as $key => $g) {
            $bic_groups[$key]['base_ttc'] = $total_ttc;
            $bic_groups[$key]['montant']  = $total_ttc * $g['taux'] / 100;
        }
        $total_bic = array_sum(array_column($bic_groups, 'montant'));

        // 6) Net à payer = TTC + BIC + Transport (proforma : pas d'avance)
        $net_a_payer = max(0, $total_ttc + $total_bic + $montant_transport);

        return view('pages.facture_proforma.print_proforma', compact(
            'facture', 'elements', 'entete',
            'total_ht', 'remise_1', 'remise_2', 'net_commercial',
            'tva_groups', 'total_taxe',
            'bic_groups', 'total_bic',
            'montant_transport', 'transport_label',
            'transport_qte', 'transport_prix',
            'total_ttc', 'net_a_payer'
        ));
    }
}
