<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Tva;
use App\Models\ElementFacture;
use App\Models\ElementFactureFournisseur;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Produit::with('tva')->orderBy('created_at', 'desc');

        if ($request->filled('famille_produit')) {
            $query->where('famille_produit', $request->famille_produit);
        }

        if ($request->filled('tva_id')) {
            $query->where('tva_id', $request->tva_id);
        }

        $collection = $query->paginate(50)->withQueryString();
        $tvas = Tva::all();

        return view('pages.produits.index', compact('collection', 'tvas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect()->route('module_produit.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => ['required', 'string', 'max:50', 'min:2', 'unique:produits,code'],
            'designation' => ['required', 'string', 'max:255', 'min:2'],
            'prix_unitaire' => ['required', 'numeric', 'min:0'],
            'unite' => ['nullable', 'string', 'max:50'],
            'qte' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'tva_id' => ['nullable', 'exists:tvas,id'],
            'type_ts' => ['nullable', 'in:AUCUN,MONTANT,POURCENTAGE'],
            'montant_ts' => ['nullable', 'numeric', 'min:0'],
            'pourcentage_ts' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description_ts' => ['nullable', 'string', 'max:500'],
            'famille_produit' => ['nullable', 'in:LOCBIE,IMPBIE,LOCSER,IMPSER'],
        ], [
            'prix_unitaire.required' => "Le prix unitaire de l'article est obligatoire.",
        ]);

        // Prestation de service : aucun stock physique (forcé à 0)
        if (in_array($validatedData['famille_produit'] ?? '', ['LOCSER', 'IMPSER'])) {
            $validatedData['qte'] = 0;
        }

        // Nettoyage cohérent des taxes spécifiques
        $typeTs = $validatedData['type_ts'] ?? 'AUCUN';
        if ($typeTs === 'AUCUN' || empty($typeTs)) {
            $validatedData['type_ts'] = 'AUCUN';
            $validatedData['montant_ts'] = 0;
            $validatedData['pourcentage_ts'] = 0;
            $validatedData['description_ts'] = null;
        } elseif ($typeTs === 'MONTANT') {
            $validatedData['type_ts'] = 'MONTANT';
            $validatedData['pourcentage_ts'] = 0;
        } elseif ($typeTs === 'POURCENTAGE') {
            $validatedData['type_ts'] = 'POURCENTAGE';
            $validatedData['montant_ts'] = 0;
        }

        $produit = Produit::create($validatedData);
        $produit->load('tva');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $produit->id,
                    'code' => $produit->code,
                    'designation' => $produit->designation,
                    'prix_unitaire' => $produit->prix_unitaire,
                    'qte' => $produit->qte,
                    'tva_taux' => (float) optional($produit->tva)->taux,
                    'text' => trim(($produit->code ? $produit->code . ' - ' : '') . $produit->designation),
                ],
            ], 201);
        }

        smilify('success', 'Produit enregistré avec succès!');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(String $id)
    {
        $item = Produit::with('tva')->findOrFail($id);
        $tvas = Tva::orderBy('code')->get();

        // Totaux ventes (factures clients)
        $ventesRow = ElementFacture::where('produits_id', $id)
            ->selectRaw('COUNT(DISTINCT facture_id) as nb_factures, SUM(quantite) as qte_vendue, SUM(quantite * prix_unitaire) as montant_ht')
            ->first();

        // Totaux achats (factures fournisseurs)
        $achatsRow = ElementFactureFournisseur::where('produits_id', $id)
            ->selectRaw('COUNT(DISTINCT facture_fournisseurs_id) as nb_factures, SUM(quantite) as qte_achetee, SUM(quantite * prix_unitaire) as montant_ht')
            ->first();

        $statsVentes = [
            'nb_factures' => (int) ($ventesRow->nb_factures ?? 0),
            'qte_vendue'  => (float) ($ventesRow->qte_vendue ?? 0),
            'montant_ht'  => (float) ($ventesRow->montant_ht ?? 0),
        ];

        $statsAchats = [
            'nb_factures' => (int) ($achatsRow->nb_factures ?? 0),
            'qte_achetee' => (float) ($achatsRow->qte_achetee ?? 0),
            'montant_ht'  => (float) ($achatsRow->montant_ht ?? 0),
        ];

        return view('pages.produits.show', compact('item', 'tvas', 'statsVentes', 'statsAchats'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return redirect()->route('module_produit.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'code' => ['required', 'string', 'max:50', 'min:2', 'unique:produits,code,' . $id],
            'designation' => ['required', 'string', 'max:255', 'min:2'],
            'prix_unitaire' => ['required', 'numeric', 'min:0'],
            'unite' => ['nullable', 'string', 'max:50'],
            'qte' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'tva_id' => ['nullable', 'exists:tvas,id'],
            'type_ts' => ['nullable', 'in:AUCUN,MONTANT,POURCENTAGE'],
            'montant_ts' => ['nullable', 'numeric', 'min:0'],
            'pourcentage_ts' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description_ts' => ['nullable', 'string', 'max:500'],
            'famille_produit' => ['nullable', 'in:LOCBIE,IMPBIE,LOCSER,IMPSER'],
        ], [
            'prix_unitaire.required' => "Le prix unitaire de l'article est obligatoire.",
        ]);

        // Prestation de service : aucun stock physique (forcé à 0)
        if (in_array($validatedData['famille_produit'] ?? '', ['LOCSER', 'IMPSER'])) {
            $validatedData['qte'] = 0;
        }

        // Nettoyage cohérent des taxes spécifiques
        $typeTs = $validatedData['type_ts'] ?? 'AUCUN';
        if ($typeTs === 'AUCUN' || empty($typeTs)) {
            $validatedData['type_ts'] = 'AUCUN';
            $validatedData['montant_ts'] = 0;
            $validatedData['pourcentage_ts'] = 0;
            $validatedData['description_ts'] = null;
        } elseif ($typeTs === 'MONTANT') {
            $validatedData['type_ts'] = 'MONTANT';
            $validatedData['pourcentage_ts'] = 0;
        } elseif ($typeTs === 'POURCENTAGE') {
            $validatedData['type_ts'] = 'POURCENTAGE';
            $validatedData['montant_ts'] = 0;
        }

        Produit::whereId($id)->update($validatedData);

        smilify('success', 'Produit modifié avec succès!');
        return redirect()->back();
    }

    /**
     * Activer ou désactiver un produit du catalogue (sans le supprimer).
     */
    public function toggleStatus($id)
    {
        $produit = Produit::findOrFail($id);
        $produit->is_active = !$produit->is_active;
        $produit->save();

        $etat = $produit->is_active ? 'activé (visible dans la boutique)' : 'désactivé (masqué des ventes)';
        smilify('success', "Le produit {$produit->designation} a été {$etat}.");
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produit = Produit::findOrFail($id);
        $produit->update(['is_active' => false]);

        smilify('success', "L'article {$produit->designation} a été désactivé du catalogue avec succès.");
        return redirect()->route('module_produit.index');
    }
}
