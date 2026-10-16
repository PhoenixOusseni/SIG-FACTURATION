<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Stock;
use App\Models\MouvementStock;

use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::all();
        return view('pages.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     * Create is handled via modal on the index page.
     */
    public function create()
    {
        return redirect()->route('gestion_service.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Service::create([
            'libelle' => $request->libelle,
            'code' => $request->code,
            'description' => $request->description,
        ]);

        smilify('success', 'Dépôt créé avec succès.');
        return redirect()->route('gestion_service.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $service = Service::findOrFail($id);

        $stocks = Stock::with('produit')
            ->where('service_id', $id)
            ->whereHas('produit')
            ->get()
            ->sortBy(fn($stock) => optional($stock->produit)->designation);

        $totalProduits  = $stocks->count();
        $totalQuantite  = $stocks->sum('quantite');
        $nbRupture      = $stocks->where('quantite', '<=', 0)->count();
        $nbFaible       = $stocks->filter(fn($s) => $s->quantite > 0 && $s->quantite < 5)->count();

        $mouvements = MouvementStock::with(['produit', 'user'])
            ->where('service_id', $id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('pages.services.show', compact(
            'service', 'stocks', 'totalProduits', 'totalQuantite', 'nbRupture', 'nbFaible', 'mouvements'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id)
    {
        $finds = Service::findOrFail($id);
        return view('pages.services.edit', compact('finds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $service = Service::findOrFail($id);
        $service->update(
            [
                'libelle' => $request->libelle,
                'description' => $request->description,
                'code' => $request->code,
            ]
        );

        return redirect()->back()
            ->with('success', 'Service modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return redirect()->route('gestion_service.index')->with('success', 'Service supprimé avec succès.');
    }
}
