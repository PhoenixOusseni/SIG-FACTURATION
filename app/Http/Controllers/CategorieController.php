<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categorie;

class CategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = Categorie::orderBy('created_at', 'desc')->paginate(10);
        // $collection = Categorie::all(); --- IGNORE ---
        return view('pages.categories.index', compact('collection'));
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
            'code' => ['required', 'string', 'max:50', 'min:2', 'unique:categories,code'],
            'libelle' => ['required', 'string', 'max:150', 'min:2'],
        ]);

        Categorie::create([
            'code' => $request->code,
            'libelle' => $request->libelle,
        ]);

        smilify('success', 'categorie enregistrée avec succès!');
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categorie = Categorie::findOrFail($id);

        return view('pages.categories.edit', compact('categorie'));
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
            'code' => ['required', 'string', 'max:50', 'min:2', 'unique:categories,code,' . $id],
            'libelle' => ['required', 'string', 'max:150', 'min:2'],
        ]);

        Categorie::whereId($id)->update($validatedData);
        smilify('success', 'Categorie modifiée avec succès!');
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
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();
        smilify('success', 'Categorie supprimée avec succès!');
        return redirect()->route('module_categorie.index');
    }
}
