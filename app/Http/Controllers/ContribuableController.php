<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Contribuable;
use App\Models\Entete;
use App\Models\Facture;
use App\Models\Reglement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContribuableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = Contribuable::paginate(10);
        $categories = Categorie::all();

        return view('pages.contribuable.index',compact('collection', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'assujeti' => 'required|string|max:255',
            'categories_id' => 'required|exists:categories,id',
            'type_client' => 'nullable|in:CC,PM,PP,PC',
            'email' => 'nullable|email|max:255',
        ]);

        $contribuable = Contribuable::create([
            'assujeti' => $request->assujeti,
            'adresse' => $request->adresse,
            'adresse_localisation' => $request->adresse_localisation,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'ifu' => $request->ifu,
            'rccm' => $request->rccm,
            'categories_id' => $request->categories_id,
            'type_client' => $request->type_client ?: 'CC',
            'division_fiscal' => $request->division_fiscal,
            'regime_fiscal' => $request->regime_fiscal,
            'adresse_cadas' => $request->adresse_cadas,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $contribuable->id,
                    'assujeti' => $contribuable->assujeti,
                    'text' => $contribuable->assujeti,
                ],
            ], 201);
        }

        smilify('success','Contribuable ajouté avec succès !');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  String  $id
     * @return \Illuminate\Http\Response
     */
    public function show(String $id)
    {
        $contribuable = Contribuable::with('Categorie')->findOrFail($id);

        // Toutes les factures du contribuable (avec règlements pour l'état)
        $factures = Facture::with(['Service', 'Reglements', 'ElementFactures.Produit.tva', 'Bic'])
            ->where('contribuable_id', $contribuable->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        // Statistiques globales
        $stats = [
            'nb_factures'    => $factures->count(),
            'montant_total'  => 0.0,
            'montant_paye'   => 0.0,
            'reste_a_payer'  => 0.0,
            'nb_soldees'     => 0,
            'nb_partielles'  => 0,
            'nb_impayees'    => 0,
        ];

        foreach ($factures as $f) {
            $total = (float) $f->montant_total;
            $paye  = (float) $f->montant_paye;
            $reste = (float) $f->reste_a_payer;

            $stats['montant_total'] += $total;
            $stats['montant_paye']  += $paye;
            $stats['reste_a_payer'] += $reste;

            $statut = $f->statut_paiement;
            if ($statut === 'soldée')      $stats['nb_soldees']++;
            elseif ($statut === 'partielle') $stats['nb_partielles']++;
            else                              $stats['nb_impayees']++;
        }

        // Tous les règlements liés aux factures de ce contribuable
        $factureIds = $factures->pluck('id');
        $reglements = Reglement::with(['Facture', 'User'])
            ->whereIn('facture_id', $factureIds)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('pages.contribuable.show', compact('contribuable', 'factures', 'stats', 'reglements'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, String $id)
    {
        $request->validate([
            'assujeti' => 'required|string|max:255',
            'categories_id' => 'required|exists:categories,id',
            'type_client' => 'nullable|in:CC,PM,PP,PC',
            'email' => 'nullable|email|max:255',
        ]);

        $contribuable = Contribuable::findOrFail($id);
        $contribuable->update([
            'assujeti' => $request->assujeti,
            'adresse' => $request->adresse,
            'adresse_localisation' => $request->adresse_localisation,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'ifu' => $request->ifu,
            'rccm' => $request->rccm,
            'categories_id' => $request->categories_id,
            'type_client' => $request->type_client ?: ($contribuable->type_client ?: 'CC'),
            'division_fiscal' => $request->division_fiscal,
            'regime_fiscal' => $request->regime_fiscal,
            'adresse_cadas' => $request->adresse_cadas,
        ]);

        smilify('success','Contribuable mis à jour avec succès !');
        return redirect()->back();
    }

    /**
     * Print the taxpayer's full record.
     */
    public function print(String $id)
    {
        $contribuable = Contribuable::with('Categorie')->findOrFail($id);

        $factures = Facture::with(['Service', 'Reglements', 'ElementFactures.Produit.tva', 'ElementFactures.Produit.bic'])
            ->where('contribuable_id', $contribuable->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $stats = [
            'nb_factures'   => $factures->count(),
            'montant_total' => 0.0,
            'montant_paye'  => 0.0,
            'reste_a_payer' => 0.0,
            'nb_soldees'    => 0,
            'nb_partielles' => 0,
            'nb_impayees'   => 0,
        ];

        foreach ($factures as $f) {
            $stats['montant_total'] += (float) $f->montant_total;
            $stats['montant_paye']  += (float) $f->montant_paye;
            $stats['reste_a_payer'] += (float) $f->reste_a_payer;

            $statut = $f->statut_paiement;
            if ($statut === 'soldée')        $stats['nb_soldees']++;
            elseif ($statut === 'partielle') $stats['nb_partielles']++;
            else                             $stats['nb_impayees']++;
        }

        $factureIds = $factures->pluck('id');
        $reglements = Reglement::with(['Facture', 'User'])
            ->whereIn('facture_id', $factureIds)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $entete = Entete::first();

        return view('pages.contribuable.print_fiche_contribuale',
            compact('contribuable', 'factures', 'reglements', 'stats', 'entete'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contribuable  $contribuable
     * @return \Illuminate\Http\Response
     */
    public function destroy(String $id)
    {
        $contribuable = Contribuable::findOrFail($id);
        $contribuable->delete();

        smilify('success','Contribuable supprimé avec succès !');
        return redirect()->route('module_contribuable.index');
    }
}
