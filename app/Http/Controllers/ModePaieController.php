<?php

namespace App\Http\Controllers;

use App\Models\ModePaie;
use Illuminate\Http\Request;

class ModePaieController extends Controller
{
    /**
     * Display a listing of all modes de paiement.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = ModePaie::orderBy('created_at', 'desc')->paginate(10);

        return view('pages.modepaie.index', compact('collection'));
    }

    /**
     * Store a newly created mode de paiement in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => ['required', 'string', 'max:50', 'min:1', 'unique:modepaies,code'],
            'libelle' => ['required', 'string', 'max:150', 'min:2'],
        ]);

        $mode = ModePaie::create([
            'code' => $request->code,
            'libelle' => $request->libelle,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $mode->id,
                    'code' => $mode->code,
                    'libelle' => $mode->libelle,
                    'text' => trim($mode->code . ($mode->libelle ? ' - ' . $mode->libelle : '')),
                ],
            ], 201);
        }

        smilify('success', 'Mode de paiement enregistré avec succès!');
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified mode de paiement.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $modepaie = ModePaie::findOrFail($id);

        return view('pages.modepaie.edit', compact('modepaie'));
    }

    /**
     * Update the specified mode de paiement in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modepaie = ModePaie::findOrFail($id);

        $this->validate($request, [
            'code' => ['required', 'string', 'max:50', 'min:1', 'unique:modepaies,code,' . $id],
            'libelle' => ['required', 'string', 'max:150', 'min:2'],
        ]);

        $modepaie->update([
            'code' => $request->code,
            'libelle' => $request->libelle,
        ]);

        smilify('success', 'Mode de paiement modifié avec succès!');
        return redirect('module_modepaie');
    }

    /**
     * Remove the specified mode de paiement from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ModePaie::findOrFail($id)->delete();

        smilify('success', 'Mode de paiement supprimé avec succès!');
        return redirect()->back();
    }
}
