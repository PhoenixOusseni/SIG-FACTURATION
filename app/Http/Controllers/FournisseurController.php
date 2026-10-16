<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Entete;
use App\Models\Fournisseur;
use App\Models\ReglementDepense;
use App\Models\ReglementFournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $collection = Fournisseur::with('Categorie')->paginate(10);
        $categories = Categorie::all();

        return view('pages.fournisseur.index', compact('collection', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'assujeti'      => ['required', 'string', 'max:255'],
            'categories_id' => ['required', 'exists:categories,id'],
        ]);

        $fournisseur = Fournisseur::create([
            'assujeti'             => $request->assujeti,
            'adresse'              => $request->adresse,
            'adresse_localisation' => $request->adresse_localisation,
            'telephone'            => $request->telephone,
            'ifu'                  => $request->ifu,
            'rccm'                 => $request->rccm,
            'categories_id'        => $request->categories_id,
            'division_fiscal'      => $request->division_fiscal,
            'regime_fiscal'        => $request->regime_fiscal,
            'adresse_cadas'        => $request->adresse_cadas,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $fournisseur->id,
                    'assujeti' => $fournisseur->assujeti,
                    'text' => $fournisseur->assujeti,
                ],
            ], 201);
        }

        smilify('success', 'Fournisseur ajouté avec succès !');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $fournisseur = Fournisseur::with('Categorie')->findOrFail($id);

        $factureIds = $fournisseur->FactureFournisseurs()->pluck('id');

        $factures = $fournisseur->FactureFournisseurs()
            ->with(['Service', 'ElementFactureFournisseurs.Produit.tva', 'ReglementFournisseurs'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $depenses = $fournisseur->Depenses()
            ->with('reglementDepenses')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $reglementsFournisseur = ReglementFournisseur::with(['FactureFournisseur', 'User'])
            ->whereIn('facture_fournisseur_id', $factureIds)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $depenseIds = $depenses->pluck('id');
        $reglementsDepenses = ReglementDepense::with(['depense', 'user'])
            ->whereIn('depense_id', $depenseIds)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $montantTotalFactures = 0.0;
        foreach ($factures as $f) {
            $montantTotalFactures += (float) $f->montant_total;
        }

        $totalDepenses      = (float) $depenses->sum('montant');
        $totalRegleFactures = (float) $reglementsFournisseur->sum('montant');
        $totalRegleDepenses = (float) $reglementsDepenses->sum('montant');
        $totalSolde         = $totalRegleFactures + $totalRegleDepenses;
        $resteASolder       = ($montantTotalFactures + $totalDepenses) - $totalSolde;

        $stats = [
            'nb_factures'          => $factures->count(),
            'montant_total'        => $montantTotalFactures,
            'nb_depenses'          => $depenses->count(),
            'total_depenses'       => $totalDepenses,
            'total_regle_factures' => $totalRegleFactures,
            'total_regle_depenses' => $totalRegleDepenses,
            'total_solde'          => $totalSolde,
            'reste_a_solder'       => $resteASolder,
        ];

        return view('pages.fournisseur.show', compact(
            'fournisseur', 'factures', 'depenses',
            'reglementsFournisseur', 'reglementsDepenses', 'stats'
        ));
    }

    public function print(string $id)
    {
        $fournisseur = Fournisseur::with('Categorie')->findOrFail($id);

        $factureIds = $fournisseur->FactureFournisseurs()->pluck('id');

        $factures = $fournisseur->FactureFournisseurs()
            ->with(['Service', 'ElementFactureFournisseurs.Produit.tva', 'ReglementFournisseurs'])
            ->orderByDesc('date')
            ->get();

        $depenses = $fournisseur->Depenses()
            ->with('reglementDepenses')
            ->orderByDesc('date')
            ->get();

        $reglementsFournisseur = ReglementFournisseur::with(['FactureFournisseur', 'User'])
            ->whereIn('facture_fournisseur_id', $factureIds)
            ->orderByDesc('date')
            ->get();

        $depenseIds = $depenses->pluck('id');
        $reglementsDepenses = ReglementDepense::with(['depense', 'user'])
            ->whereIn('depense_id', $depenseIds)
            ->orderByDesc('date')
            ->get();

        $montantTotalFactures = 0.0;
        foreach ($factures as $f) {
            $montantTotalFactures += (float) $f->montant_total;
        }

        $totalDepenses      = (float) $depenses->sum('montant');
        $totalRegleFactures = (float) $reglementsFournisseur->sum('montant');
        $totalRegleDepenses = (float) $reglementsDepenses->sum('montant');
        $totalSolde         = $totalRegleFactures + $totalRegleDepenses;
        $resteASolder       = ($montantTotalFactures + $totalDepenses) - $totalSolde;

        $stats = [
            'nb_factures'          => $factures->count(),
            'montant_total'        => $montantTotalFactures,
            'nb_depenses'          => $depenses->count(),
            'total_depenses'       => $totalDepenses,
            'total_regle_factures' => $totalRegleFactures,
            'total_regle_depenses' => $totalRegleDepenses,
            'total_solde'          => $totalSolde,
            'reste_a_solder'       => $resteASolder,
        ];

        $entete = Entete::first();

        return view('pages.fournisseur.print_fiche_fournisseur', compact(
            'fournisseur', 'factures', 'depenses',
            'reglementsFournisseur', 'reglementsDepenses', 'stats', 'entete'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'assujeti'      => ['required', 'string', 'max:255'],
            'categories_id' => ['required', 'exists:categories,id'],
        ]);

        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->update([
            'assujeti'             => $request->assujeti,
            'adresse'              => $request->adresse,
            'adresse_localisation' => $request->adresse_localisation,
            'telephone'            => $request->telephone,
            'ifu'                  => $request->ifu,
            'rccm'                 => $request->rccm,
            'categories_id'        => $request->categories_id,
            'division_fiscal'      => $request->division_fiscal,
            'regime_fiscal'        => $request->regime_fiscal,
            'adresse_cadas'        => $request->adresse_cadas,
        ]);

        smilify('success', 'Fournisseur mis à jour avec succès !');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->delete();

        smilify('success', 'Fournisseur supprimé avec succès !');
        return redirect()->route('module_fournisseur.index');
    }
}
