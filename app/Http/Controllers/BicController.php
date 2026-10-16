<?php

namespace App\Http\Controllers;

use App\Models\Bic;
use Illuminate\Http\Request;

class BicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $collection = Bic::orderBy('created_at', 'desc')->paginate(10);
        return view('pages.bic.index', compact('collection'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Bic::findOrFail($id);
        $produits = $item->produits()->orderBy('created_at', 'desc')->paginate(10);
        return view('pages.bic.show', compact('item', 'produits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code'     => ['required', 'string', 'max:50', 'min:2', 'unique:bics,code'],
            'intitule' => ['required', 'string', 'max:150', 'min:2'],
            'taux'     => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Bic::create([
            'code'     => $request->code,
            'intitule' => $request->intitule,
            'taux'     => $request->taux,
        ]);

        smilify('success', 'BIC enregistré avec succès!');
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'code'     => ['required', 'string', 'max:50', 'min:2', 'unique:bics,code,' . $id],
            'intitule' => ['required', 'string', 'max:150', 'min:2'],
            'taux'     => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Bic::whereId($id)->update($validatedData);
        smilify('success', 'BIC modifié avec succès!');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $bic = Bic::findOrFail($id);
        $bic->delete();
        smilify('success', 'BIC supprimé avec succès!');
        return redirect()->route('module_bic.index');
    }
}
