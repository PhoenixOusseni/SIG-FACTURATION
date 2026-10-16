<?php

namespace App\Http\Controllers;

use App\Models\Typefacture;
use Illuminate\Http\Request;

class TypefactureController extends Controller
{
    /**
     * Display a listing of all type de factures.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = Typefacture::orderBy('created_at', 'desc')->paginate(10);

        return view('pages.typefacture.index', compact('collection'));
    }

    /**
     * Store a newly created type de facture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => ['required', 'string', 'max:50', 'min:1', 'unique:typefactures,code'],
            'libelle' => ['required', 'string', 'max:150', 'min:2'],
        ]);

        Typefacture::create([
            'code' => $request->code,
            'libelle' => $request->libelle,
        ]);

        smilify('success', 'Type de facture enregistré avec succès!');
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified type de facture.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $typefacture = Typefacture::findOrFail($id);

        return view('pages.typefacture.edit', compact('typefacture'));
    }

    /**
     * Update the specified type de facture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $typefacture = Typefacture::findOrFail($id);

        $this->validate($request, [
            'code' => ['required', 'string', 'max:50', 'min:1', 'unique:typefactures,code,' . $id],
            'libelle' => ['required', 'string', 'max:150', 'min:2'],
        ]);

        $typefacture->update([
            'code' => $request->code,
            'libelle' => $request->libelle,
        ]);

        smilify('success', 'Type de facture modifié avec succès!');
        return redirect('module_typefacture');
    }

    /**
     * Remove the specified type de facture from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Typefacture::findOrFail($id)->delete();

        smilify('success', 'Type de facture supprimé avec succès!');
        return redirect()->back();
    }
}
