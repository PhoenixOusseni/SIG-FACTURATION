<?php

namespace App\Http\Controllers\Comptoir;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\Categorie;
use App\Models\Contribuable;
use App\Models\ModePaie;
use App\Models\Produit;
use App\Models\User;
use App\Services\Comptoir\TicketMoney;
use Illuminate\Http\Request;

class ComptoirMetaController extends Controller
{
    public function meta(Request $request)
    {
        $modepaies = ModePaie::orderBy('libelle')->get(['id', 'code', 'libelle']);
        $categories = Categorie::orderBy('libelle')->get(['id', 'libelle']);

        // Fallback if categories use different column
        if ($categories->isEmpty()) {
            $categories = Categorie::query()->get()->map(function ($c) {
                return [
                    'id' => $c->id,
                    'libelle' => $c->libelle ?? $c->designation ?? $c->nom ?? ('#' . $c->id),
                ];
            });
        }

        $sellers = User::query()
            ->orderBy('id')
            ->get()
            ->filter(function (User $u) {
                return $u->isCaissierComptoir();
            })
            ->map(function (User $u) {
                return [
                    'id' => $u->id,
                    'label' => trim(($u->prenom ?? '') . ' ' . ($u->nom ?? $u->name ?? '')),
                    'role' => $u->role,
                ];
            })
            ->values();

        $defaultCaisse = Caisse::where('active', true)->orderBy('id')->first();

        return response()->json([
            'success' => true,
            'modepaies' => $modepaies,
            'categories' => $categories,
            'sellers' => $sellers,
            'default_caisse_id' => optional($defaultCaisse)->id,
            'money_tolerance' => TicketMoney::tolerance(),
            'current_user_id' => optional($request->user())->id,
        ]);
    }

    public function searchProduits(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $barcode = trim((string) $request->query('barcode', ''));
        $categorieId = $request->query('categorie_id');
        $limit = (int) $request->query('limit', config('comptoir.product_search_limit', 50));
        $limit = max(1, min(100, $limit));

        $query = Produit::with('tva')->orderBy('designation');

        if ($barcode !== '') {
            $query->where(function ($w) use ($barcode) {
                $w->where('code', $barcode);
                if (\Illuminate\Support\Facades\Schema::hasColumn('produits', 'barcode')) {
                    $w->orWhere('barcode', $barcode);
                }
            });
        } elseif ($q !== '') {
            // Prefer exact code (scanner), else fuzzy designation/code.
            $exactIds = Produit::query()->where('code', $q);
            if (\Illuminate\Support\Facades\Schema::hasColumn('produits', 'barcode')) {
                $exactIds->orWhere('barcode', $q);
            }
            $ids = $exactIds->pluck('id');
            if ($ids->isNotEmpty()) {
                $query->whereIn('id', $ids);
            } else {
                $query->where(function ($w) use ($q) {
                    $w->where('designation', 'like', '%' . $q . '%')
                        ->orWhere('code', 'like', '%' . $q . '%');
                });
            }
        }

        // famille_produit may act as category proxy if no categorie_id on produits
        if ($categorieId && \Illuminate\Support\Facades\Schema::hasColumn('produits', 'categorie_id')) {
            $query->where('categorie_id', $categorieId);
        }

        $items = $query->limit($limit)->get()->map(function (Produit $p) {
            return [
                'id' => $p->id,
                'code' => $p->code,
                'designation' => $p->designation,
                'prix_ht' => (float) ($p->prix_unitaire ?? 0),
                'tva_taux' => (float) optional($p->tva)->taux,
                'stock_disponible' => (float) ($p->qte ?? 0),
                'unite' => $p->unite,
            ];
        });

        return response()->json([
            'success' => true,
            'produits' => $items,
        ]);
    }

    public function searchContribuables(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $limit = max(1, min(50, (int) $request->query('limit', 20)));

        $query = Contribuable::query()->orderBy('assujeti');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('assujeti', 'like', '%' . $q . '%');
                if (\Illuminate\Support\Facades\Schema::hasColumn('contribuables', 'ifu')) {
                    $w->orWhere('ifu', 'like', '%' . $q . '%');
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('contribuables', 'telephone')) {
                    $w->orWhere('telephone', 'like', '%' . $q . '%');
                }
            });
        }

        $items = $query->limit($limit)->get()->map(function (Contribuable $c) {
            return [
                'id' => $c->id,
                'assujeti' => $c->assujeti,
                'ifu' => $c->ifu ?? null,
                'telephone' => $c->telephone ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'contribuables' => $items,
        ]);
    }
}
