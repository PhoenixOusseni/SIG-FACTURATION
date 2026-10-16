<?php

namespace App\Http\Controllers;

use App\Models\Tva;
use Illuminate\Http\Request;

class TvaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = Tva::orderBy('created_at', 'desc')->paginate(10);
        return view('pages.tva.index', compact('collection'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $item = Tva::findOrFail($id);
        $produits = $item->produits()->orderBy('created_at', 'desc')->paginate(10);
        return view('pages.tva.show', compact('item', 'produits'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code'       => ['required', 'string', 'max:50', 'min:1', 'unique:tvas,code'],
            'intitule'   => ['required', 'string', 'max:150', 'min:1'],
            'compte_tva' => ['nullable', 'integer'],
            'taux'       => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Tva::create([
            'code'       => $request->code,
            'intitule'   => $request->intitule,
            'compte_tva' => $request->compte_tva,
            'taux'       => $request->taux,
        ]);

        smilify('success', 'TVA enregistrée avec succès!');
        return redirect()->back();
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
            'code'       => ['required', 'string', 'max:50', 'min:1', 'unique:tvas,code,' . $id],
            'intitule'   => ['required', 'string', 'max:150', 'min:1'],
            'compte_tva' => ['nullable', 'integer'],
            'taux'       => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Tva::whereId($id)->update($validatedData);
        smilify('success', 'TVA modifiée avec succès!');
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
        $tva = Tva::findOrFail($id);
        $tva->delete();
        smilify('success', 'TVA supprimée avec succès!');
        return redirect()->route('module_tva.index');
    }
}