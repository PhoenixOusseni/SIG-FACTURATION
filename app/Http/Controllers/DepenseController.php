<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Models\Fournisseur;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $collection   = Depense::with('fournisseur')->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(10);
        $fournisseurs = Fournisseur::orderBy('assujeti', 'asc')->get();
        $total        = Depense::sum('montant');

        return view('pages.depense.index', compact('collection', 'fournisseurs', 'total'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item         = Depense::with(['fournisseur', 'reglementDepenses.user'])->findOrFail($id);
        $fournisseurs = Fournisseur::orderBy('assujeti', 'asc')->get();

        return view('pages.depense.show', compact('item', 'fournisseurs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'motif'           => ['required', 'string', 'max:255', 'min:2'],
            'date'            => ['required', 'date'],
            'montant'         => ['required', 'numeric', 'min:0'],
            'fournisseurs_id' => ['nullable', 'exists:fournisseurs,id'],
        ]);

        Depense::create([
            'motif'           => $request->motif,
            'date'            => $request->date,
            'montant'         => $request->montant,
            'fournisseurs_id' => $request->fournisseurs_id,
        ]);

        smilify('success', 'Dépense enregistrée avec succès!');
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'motif'           => ['required', 'string', 'max:255', 'min:2'],
            'date'            => ['required', 'date'],
            'montant'         => ['required', 'numeric', 'min:0'],
            'fournisseurs_id' => ['nullable', 'exists:fournisseurs,id'],
        ]);

        Depense::whereId($id)->update($validatedData);
        smilify('success', 'Dépense modifiée avec succès!');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $depense = Depense::findOrFail($id);
        $depense->delete();
        smilify('success', 'Dépense supprimée avec succès!');
        return redirect()->route('module_depense.index');
    }
}
