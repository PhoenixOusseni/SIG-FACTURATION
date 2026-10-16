<?php

namespace App\Http\Controllers;

use App\Models\TransfertStock;
use App\Models\ElementTransfertStock;
use App\Models\Stock;
use App\Models\Produit;
use App\Models\Service;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransfertStockController extends Controller
{
    public function index()
    {
        $collection = TransfertStock::with(['serviceSource', 'serviceDestination', 'user'])
            ->orderByDesc('id')
            ->paginate(15);

        $services = Service::orderBy('libelle')->get();
        $produits = Produit::orderBy('code')->get();

        return view('pages.transferts.index', compact('collection', 'services', 'produits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_source_id'      => 'required|exists:services,id',
            'service_destination_id' => 'required|exists:services,id|different:service_source_id',
            'date'                   => 'nullable|date',
            'produits_id'            => 'required|array|min:1',
            'produits_id.*'          => 'required|exists:produits,id',
            'quantite'               => 'required|array|min:1',
            'quantite.*'             => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($request) {
            $year = date('Y');
            $last = TransfertStock::whereYear('created_at', $year)->orderByDesc('id')->first();
            $next = ($last ? intval(substr($last->code, 4, 5)) : 0) + 1;
            $code = 'TRF-' . str_pad($next, 5, '0', STR_PAD_LEFT) . '/' . $year;

            $transfert = TransfertStock::create([
                'code'                   => $code,
                'service_source_id'      => $request->service_source_id,
                'service_destination_id' => $request->service_destination_id,
                'date'                   => $request->date ?? now()->toDateString(),
                'note'                   => $request->note,
                'statut'                 => 'en_attente',
                'user_id'                => auth()->id(),
            ]);

            foreach ($request->produits_id as $i => $produitId) {
                $qte = (float) ($request->quantite[$i] ?? 0);
                if ($produitId && $qte > 0) {
                    ElementTransfertStock::create([
                        'transfert_stock_id' => $transfert->id,
                        'produit_id'         => $produitId,
                        'quantite'           => $qte,
                    ]);
                }
            }
        });

        smilify('success', 'Transfert créé. Validez-le pour appliquer les mouvements de stock.');
        return redirect()->route('gestion_transfert.index');
    }

    public function show($id)
    {
        $transfert = TransfertStock::with([
            'serviceSource',
            'serviceDestination',
            'user',
            'elements.produit',
        ])->findOrFail($id);

        return view('pages.transferts.show', compact('transfert'));
    }

    public function valider($id)
    {
        $transfert = TransfertStock::with('elements.produit')->findOrFail($id);

        if ($transfert->statut !== 'en_attente') {
            smilify('error', 'Ce transfert ne peut plus être validé.');
            return redirect()->route('gestion_transfert.show', $id);
        }

        DB::transaction(function () use ($transfert) {
            foreach ($transfert->elements as $el) {
                StockService::transferer(
                    (int) $el->produit_id,
                    (int) $transfert->service_source_id,
                    (int) $transfert->service_destination_id,
                    (float) $el->quantite,
                    $transfert->id
                );
            }
            $transfert->update(['statut' => 'valide']);
        });

        smilify('success', 'Transfert validé et stock mis à jour.');
        return redirect()->route('gestion_transfert.show', $id);
    }

    public function annuler($id)
    {
        $transfert = TransfertStock::findOrFail($id);

        if ($transfert->statut !== 'en_attente') {
            smilify('error', 'Seuls les transferts en attente peuvent être annulés.');
            return redirect()->route('gestion_transfert.show', $id);
        }

        $transfert->update(['statut' => 'annule']);

        smilify('success', 'Transfert annulé.');
        return redirect()->route('gestion_transfert.show', $id);
    }
}
