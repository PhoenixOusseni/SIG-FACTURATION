<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Service;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::with(['produit', 'service'])->orderBy('service_id');

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('produit', fn($q) => $q
                ->where('code', 'like', "%$search%")
                ->orWhere('designation', 'like', "%$search%")
            );
        }

        $stocks    = $query->paginate(20)->withQueryString();
        $services  = Service::orderBy('libelle')->get();
        $produits  = Produit::orderBy('code')->get();
        $stockQuantities = Stock::query()
            ->get(['produit_id', 'service_id', 'quantite'])
            ->mapWithKeys(fn($stock) => [
                $stock->produit_id . '_' . $stock->service_id => (float) $stock->quantite,
            ]);
        $mouvements = MouvementStock::with(['produit', 'service', 'user'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('pages.stock.index', compact('stocks', 'services', 'produits', 'stockQuantities', 'mouvements'));
    }

    public function entree(Request $request)
    {
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'note'         => 'nullable|string|max:255',
            'produit_id'   => 'required|array|min:1',
            'produit_id.*' => 'required|exists:produits,id',
            'quantite'     => 'required|array|min:1',
            'quantite.*'   => 'required|numeric|min:0.001',
        ]);

        $note  = $request->note ?: 'Entrée en stock (approvisionnement)';
        $count = 0;

        DB::transaction(function () use ($request, $note, &$count) {
            foreach ($request->produit_id as $i => $produitId) {
                if (!$produitId) continue;
                $qte = (float) ($request->quantite[$i] ?? 0);
                if ($qte <= 0) continue;
                StockService::entree(
                    (int) $produitId,
                    (int) $request->service_id,
                    $qte,
                    'entree_manuelle',
                    null,
                    $note
                );
                $count++;
            }
        });

        smilify('success', "Entrée en stock enregistrée pour $count produit(s).");
        return redirect()->route('gestion_stock.index');
    }

    public function sortie(Request $request)
    {
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'note'         => 'nullable|string|max:255',
            'produit_id'   => 'required|array|min:1',
            'produit_id.*' => 'required|exists:produits,id',
            'quantite'     => 'required|array|min:1',
            'quantite.*'   => 'required|numeric|min:0.001',
        ]);

        // Vérifier le stock disponible de chaque ligne avant d'appliquer quoi que ce soit
        $insuffisants = [];
        foreach ($request->produit_id as $i => $produitId) {
            if (!$produitId) continue;
            $qte = (float) ($request->quantite[$i] ?? 0);
            if ($qte <= 0) continue;

            $stockActuel = (float) (Stock::where('produit_id', $produitId)
                ->where('service_id', $request->service_id)
                ->value('quantite') ?? 0);

            if ($qte > $stockActuel) {
                $code = Produit::find($produitId)->code ?? $produitId;
                $insuffisants[] = "$code (disponible : " . number_format($stockActuel, 2, ',', ' ') . ")";
            }
        }

        if (!empty($insuffisants)) {
            smilify('error', 'Quantité insuffisante en stock pour : ' . implode(', ', $insuffisants) . '.');
            return redirect()->route('gestion_stock.index');
        }

        $note  = $request->note ?: 'Sortie de stock';
        $count = 0;

        DB::transaction(function () use ($request, $note, &$count) {
            foreach ($request->produit_id as $i => $produitId) {
                if (!$produitId) continue;
                $qte = (float) ($request->quantite[$i] ?? 0);
                if ($qte <= 0) continue;
                StockService::sortie(
                    (int) $produitId,
                    (int) $request->service_id,
                    $qte,
                    'sortie_manuelle',
                    null,
                    $note
                );
                $count++;
            }
        });

        smilify('success', "Sortie de stock enregistrée pour $count produit(s).");
        return redirect()->route('gestion_stock.index');
    }

    public function ajustement(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'service_id' => 'required|exists:services,id',
            'quantite'   => 'required|numeric|min:0',
            'note'       => 'nullable|string|max:255',
        ]);

        StockService::ajustement(
            (int) $request->produit_id,
            (int) $request->service_id,
            (float) $request->quantite,
            $request->note ?? 'Ajustement manuel'
        );

        smilify('success', 'Ajustement de stock enregistré.');
        return redirect()->route('gestion_stock.index');
    }

    public function ajustementGroupe(Request $request)
    {
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'note'         => 'nullable|string|max:255',
            'produit_id'   => 'required|array|min:1',
            'produit_id.*' => 'required|exists:produits,id',
            'quantite'     => 'required|array|min:1',
            'quantite.*'   => 'required|numeric|min:0',
        ]);

        $note  = $request->note ?: 'Inventaire groupé';
        $count = 0;

        DB::transaction(function () use ($request, $note, &$count) {
            foreach ($request->produit_id as $i => $produitId) {
                if (!$produitId) continue;
                $qte = (float) ($request->quantite[$i] ?? 0);
                StockService::ajustement(
                    (int) $produitId,
                    (int) $request->service_id,
                    $qte,
                    $note
                );
                $count++;
            }
        });

        smilify('success', "$count produit(s) ajusté(s) avec succès.");
        return redirect()->route('gestion_stock.index');
    }
}
