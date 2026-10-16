<?php

namespace App\Http\Controllers;

use App\Models\FactureFournisseur;
use App\Models\ElementFactureFournisseur;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Service;
use App\Models\Entete;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureFournisseurController extends Controller
{
    /**
     * Liste des factures fournisseurs.
     */
    public function index()
    {
        $collection = FactureFournisseur::with(['Fournisseur', 'Service', 'User'])
            ->orderByDesc('id')
            ->get();

        $fournisseurs = Fournisseur::orderBy('assujeti')->get();
        $produits     = Produit::orderBy('code')->get();
        $services     = Service::orderBy('libelle')->get();
        $categories   = \App\Models\Categorie::orderBy('code')->get();
        $tvas         = \App\Models\Tva::orderBy('code')->get();
        $bics         = \App\Models\Bic::orderBy('code')->get();

        return view('pages.facture_fournisseur.index', compact(
            'collection', 'fournisseurs', 'produits', 'services',
            'categories', 'tvas', 'bics'
        ));
    }

    /**
     * Enregistrer une nouvelle facture fournisseur + ses éléments.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fournisseurs_id' => 'required|exists:fournisseurs,id',
            'service_id'      => 'nullable|exists:services,id',
            'date'            => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {
            $facture = FactureFournisseur::create([
                'code'            => $request->code,
                'reference'       => $request->reference,
                'date'            => $request->date,
                'statut'          => $request->statut ?? 'en attente',
                'users_id'        => $request->users_id ?? auth()->id(),
                'fournisseurs_id' => $request->fournisseurs_id,
                'service_id'      => $request->service_id,
                'objet'           => $request->objet,
            ]);

            $this->saveElements($facture, $request);

            // Incrémenter le stock global du produit (achat fournisseur)
            $produits  = (array) $request->input('produits_id', []);
            $quantites = (array) $request->input('quantite', []);
            foreach ($produits as $i => $produitId) {
                $qte = (float) ($quantites[$i] ?? 0);
                if ($produitId && $qte > 0) {
                    Produit::where('id', $produitId)->increment('qte', $qte);
                }
            }
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture fournisseur enregistrée avec succès.');
        }

        return redirect()->route('module_facture_fournisseur.index');
    }

    /**
     * Afficher une facture fournisseur.
     */
    public function show($id)
    {
        $facture  = FactureFournisseur::with(['Fournisseur', 'Service', 'User'])->findOrFail($id);
        $elements = ElementFactureFournisseur::with('Produit.tva')
            ->where('facture_fournisseurs_id', $facture->id)
            ->get();

        $montant_total = $elements->sum(function ($element) {
            return $element->montant_total ?? ($element->quantite * $element->prix_unitaire);
        });

        return view('pages.facture_fournisseur.show', compact('facture', 'elements', 'montant_total'));
    }

    /**
     * Formulaire d'édition.
     */
    public function edit($id)
    {
        $facture  = FactureFournisseur::with(['Fournisseur', 'Service'])->findOrFail($id);
        $elements = ElementFactureFournisseur::with('Produit')
            ->where('facture_fournisseurs_id', $facture->id)
            ->get();

        $fournisseurs = Fournisseur::orderBy('assujeti')->get();
        $produits     = Produit::orderBy('code')->get();
        $services     = Service::orderBy('libelle')->get();

        return view('pages.facture_fournisseur.edit', compact(
            'facture', 'elements', 'fournisseurs', 'produits', 'services'
        ));
    }

    /**
     * Mettre à jour la facture fournisseur + ses éléments.
     */
    public function update(Request $request, $id)
    {
        $facture = FactureFournisseur::findOrFail($id);

        $request->validate([
            'fournisseurs_id' => 'required|exists:fournisseurs,id',
            'service_id'      => 'nullable|exists:services,id',
            'date'            => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $facture) {
            $facture->update([
                'code'            => $request->code ?? $facture->code,
                'reference'       => $request->reference,
                'date'            => $request->date,
                'statut'          => $request->statut ?? $facture->statut,
                'users_id'        => $request->users_id ?? $facture->users_id,
                'fournisseurs_id' => $request->fournisseurs_id ?? $facture->fournisseurs_id,
                'service_id'      => $request->service_id ?? $facture->service_id,
                'objet'           => $request->objet ?? $facture->objet,
            ]);

            $this->syncElements($facture, $request);
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture fournisseur mise à jour avec succès.');
        }

        return redirect()->route('module_facture_fournisseur.show', $facture->id);
    }

    /**
     * Supprimer une facture fournisseur (avec ses éléments).
     */
    public function destroy($id)
    {
        $facture = FactureFournisseur::findOrFail($id);

        // Rétablir le stock global avant suppression
        $elements = ElementFactureFournisseur::where('facture_fournisseurs_id', $facture->id)->get();
        foreach ($elements as $el) {
            if ($el->produits_id && (float) $el->quantite > 0) {
                Produit::where('id', $el->produits_id)->decrement('qte', (float) $el->quantite);
            }
        }

        ElementFactureFournisseur::where('facture_fournisseurs_id', $facture->id)->delete();
        $facture->delete();

        if (function_exists('smilify')) {
            smilify('success', 'Facture fournisseur supprimée avec succès.');
        }

        return redirect()->route('module_facture_fournisseur.index');
    }

    /**
     * Création des éléments depuis le payload "tableau" du formulaire.
     */
    protected function saveElements(FactureFournisseur $facture, Request $request): void
    {
        $produits     = (array) $request->input('produits_id', []);
        $designations = (array) $request->input('designation', []);
        $quantites    = (array) $request->input('quantite', []);
        $prix         = (array) $request->input('prix_unitaire', []);

        $count = max(count($produits), count($quantites), count($prix));

        for ($i = 0; $i < $count; $i++) {
            $produitId = $produits[$i] ?? null;
            $quantite  = isset($quantites[$i]) ? (float) $quantites[$i] : 0;
            $prixU     = isset($prix[$i]) ? (float) $prix[$i] : 0;

            if (!$produitId && $quantite == 0 && $prixU == 0) {
                continue;
            }

            $montant = $quantite * $prixU;

            ElementFactureFournisseur::create([
                'facture_fournisseurs_id' => $facture->id,
                'produits_id'             => $produitId,
                'designation'             => $designations[$i] ?? null,
                'quantite'                => $quantite,
                'prix_unitaire'           => $prixU,
                'montant_total'           => $montant,
            ]);
        }
    }

    /**
     * Synchroniser les éléments en édition (upsert via element_id[]).
     */
    protected function syncElements(FactureFournisseur $facture, Request $request): void
    {
        $elementIds   = (array) $request->input('element_id', []);
        $produits     = (array) $request->input('produits_id', []);
        $designations = (array) $request->input('designation', []);
        $quantites    = (array) $request->input('quantite', []);
        $prix         = (array) $request->input('prix_unitaire', []);

        $keptIds = [];
        $count   = max(count($produits), count($quantites), count($prix));

        for ($i = 0; $i < $count; $i++) {
            $produitId = $produits[$i] ?? null;
            $quantite  = isset($quantites[$i]) ? (float) $quantites[$i] : 0;
            $prixU     = isset($prix[$i]) ? (float) $prix[$i] : 0;

            if (!$produitId && $quantite == 0 && $prixU == 0) {
                continue;
            }

            $montant = $quantite * $prixU;

            $data = [
                'facture_fournisseurs_id' => $facture->id,
                'produits_id'             => $produitId,
                'designation'             => $designations[$i] ?? null,
                'quantite'                => $quantite,
                'prix_unitaire'           => $prixU,
                'montant_total'           => $montant,
            ];

            $existingId = $elementIds[$i] ?? null;
            if ($existingId) {
                $element = ElementFactureFournisseur::find($existingId);
                if ($element) {
                    $element->update($data);
                    $keptIds[] = $element->id;
                    continue;
                }
            }

            $element   = ElementFactureFournisseur::create($data);
            $keptIds[] = $element->id;
        }

        ElementFactureFournisseur::where('facture_fournisseurs_id', $facture->id)
            ->whereNotIn('id', $keptIds)
            ->delete();
    }

    /**
     * Vue d'impression d'une facture fournisseur.
     */
    public function print($id)
    {
        $facture  = FactureFournisseur::with(['Fournisseur', 'Service', 'User'])->findOrFail($id);
        $elements = ElementFactureFournisseur::with(['Produit.tva'])
            ->where('facture_fournisseurs_id', $facture->id)
            ->get();
        $entete = Entete::first();

        // Totaux
        $total_ht   = 0.0;
        $tva_groups = []; // [taux => ['taux','base','taxe']]

        foreach ($elements as $element) {
            $quantite  = (float) $element->quantite;
            $prix      = (float) $element->prix_unitaire;
            $montantHt = $quantite * $prix;
            $tauxTva   = (float) optional(optional($element->Produit)->tva)->taux;
            $taxe      = $montantHt * $tauxTva / 100;

            $total_ht += $montantHt;

            $tvaKey = (string) $tauxTva;
            if (!isset($tva_groups[$tvaKey])) {
                $tva_groups[$tvaKey] = ['taux' => $tauxTva, 'base' => 0, 'taxe' => 0];
            }
            $tva_groups[$tvaKey]['base'] += $montantHt;
            $tva_groups[$tvaKey]['taxe'] += $taxe;
        }

        $total_taxe = array_sum(array_column($tva_groups, 'taxe'));
        $total_ttc  = $total_ht + $total_taxe;

        return view('pages.facture_fournisseur.print', compact(
            'facture', 'elements', 'entete',
            'total_ht', 'tva_groups', 'total_taxe', 'total_ttc'
        ));
    }
}
