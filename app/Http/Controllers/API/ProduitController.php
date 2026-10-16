<?php

namespace App\Http\Controllers\API;

use App\Models\Produit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProduitController extends Controller
{

    public function search(Request $request)
    {
        $query = $request->input('q');
        $produits = Produit::where('nom_complet', 'like', "%{$query}%")->get();
    
        // Charger la relation de stock
        $produits->load('entrees');
    
        $results = [];
        foreach ($produits as $produit) {
            $results[] = [
                'id' => $produit->id,
                'text' => $produit->nom_complet,
                'prix' => $produit->prix_unitaire,
                'tva' => $produit->tva,
                'reduction' => $produit->reduction,
                'stock' => $produit->entrees[0]->quantity,
                'disabled' => $produit->entrees[0]->quantity == 0 ? true : false
            ];
        }
    
        return response()->json([
            'items' => $results,
            'total_count' => count($results)
        ]);
    }
    
}
