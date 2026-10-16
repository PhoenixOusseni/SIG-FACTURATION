<?php

namespace App\Http\Controllers\Comptoir;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\Service;
use Illuminate\Http\Request;

class CaisseController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isSuperviseurComptoir()) {
            abort(403);
        }

        $caisses = Caisse::with('Service')->orderBy('libelle')->get();
        $services = Service::orderBy('libelle')->orderBy('id')->get();

        return view('pages.comptoir.caisses.index', compact('caisses', 'services'));
    }

    public function store(Request $request)
    {
        if (!$request->user()->isSuperviseurComptoir()) {
            abort(403);
        }

        $data = $request->validate([
            'code' => 'required|string|max:20|unique:caisses,code',
            'libelle' => 'required|string|max:100',
            'service_id' => 'required|exists:services,id',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $caisse = Caisse::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'caisse' => $caisse]);
        }

        return redirect()->route('module_caisse.index')->with('success', 'Caisse créée.');
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->isSuperviseurComptoir()) {
            abort(403);
        }

        $caisse = Caisse::findOrFail($id);
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:caisses,code,' . $caisse->id,
            'libelle' => 'required|string|max:100',
            'service_id' => 'required|exists:services,id',
            'active' => 'nullable|boolean',
        ]);
        $data['active'] = $request->boolean('active', $caisse->active);
        $caisse->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'caisse' => $caisse]);
        }

        return redirect()->route('module_caisse.index')->with('success', 'Caisse mise à jour.');
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isSuperviseurComptoir()) {
            abort(403);
        }

        $caisse = Caisse::findOrFail($id);
        if ($caisse->sessionOuverte()) {
            return redirect()->back()->with('form_error', 'Impossible de supprimer une caisse avec session ouverte.');
        }
        $caisse->delete();

        return redirect()->route('module_caisse.index')->with('success', 'Caisse supprimée.');
    }

    public function show($id)
    {
        return redirect()->route('module_caisse.index');
    }
}
