<?php

namespace App\Http\Controllers;

use App\Models\BordereauLivraison;
use App\Models\ElementBordereauLivraison;
use App\Models\FactureProforma;
use App\Models\ElementFactureProforma;
use App\Models\Facture;
use App\Models\ElementFacture;
use App\Models\Contribuable;
use App\Models\Produit;
use App\Models\Entete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BordereauLivraisonController extends Controller
{
    /* ============================================================
     |  LISTING
     * ============================================================ */
    public function index()
    {
        $collection = BordereauLivraison::with(['Contribuable', 'Service', 'Categorie', 'FactureProforma', 'Facture'])
            ->orderByDesc('id')
            ->get();

        $contribuables = Contribuable::all();
        $produits      = Produit::orderBy('code')->get();
        $proformas     = FactureProforma::whereNull('bordereau_livraison_id')
            ->orWhere('statut', '!=', 'transformée')
            ->orderByDesc('id')
            ->get();

        return view('pages.bordereau_livraison.index', compact('collection', 'contribuables', 'produits', 'proformas'));
    }

    public function all_bordereau()
    {
        $collection = BordereauLivraison::with(['Contribuable', 'Service', 'Categorie', 'FactureProforma', 'Facture'])
            ->orderByDesc('id')
            ->get();

        return view('pages.bordereau_livraison.liste', compact('collection'));
    }

    /* ============================================================
     |  CRÉATION DIRECTE (sans proforma)
     * ============================================================ */
    public function store(Request $request)
    {
        $request->validate([
            'contribuables_id' => 'required|exists:contribuables,id',
            'service_id'       => 'required|exists:services,id',
        ]);

        $bordereau = null;

        DB::transaction(function () use ($request, &$bordereau) {
            $bordereau = BordereauLivraison::create([
                'code'             => $this->generateCode(),
                'reference'        => $request->reference,
                'date'             => $request->date ?? now()->toDateString(),
                'statut'           => 'en attente',
                'transporteur'     => $request->transporteur,
                'livreur'          => $request->livreur,
                'observation'      => $request->observation,
                'users_id'         => auth()->id(),
                'contribuables_id' => $request->contribuables_id,
                'service_id'       => $request->service_id,
                'categorie_id'     => $request->categorie_id,
                'remise_1'         => $request->remise_1 ?? 0,
                'remise_2'         => $request->remise_2 ?? 0,
            ]);

            $this->saveElements($bordereau, $request);
        });

        smilify('success', 'Bordereau de livraison enregistré avec succès.');
        return redirect()->route('module_bordereau_livraison.show', $bordereau->id);
    }

    /* ============================================================
     |  TRANSFORMATION : Proforma -> Bordereau de livraison
     * ============================================================ */
    public function createFromProforma($proformaId)
    {
        $proforma = FactureProforma::with('ElementFactureProformas')->findOrFail($proformaId);

        if ($proforma->statut === 'transformée' || $proforma->bordereau_livraison_id) {
            smilify('error', 'Cette facture proforma a déjà été transformée en bordereau de livraison.');
            return redirect()->back();
        }

        $bordereau = null;

        DB::transaction(function () use ($proforma, &$bordereau) {
            $bordereau = BordereauLivraison::create([
                'code'                => $this->generateCode(),
                'reference'           => $proforma->reference,
                'date'                => now()->toDateString(),
                'statut'              => 'en attente',
                'facture_proforma_id' => $proforma->id,
                'users_id'            => auth()->id(),
                'contribuables_id'    => $proforma->contribuables_id,
                'service_id'          => $proforma->service_id,
                'objet'               => $proforma->objet,
                'remise_1'            => $proforma->remise_1 ?? 0,
                'remise_2'            => $proforma->remise_2 ?? 0,
            ]);

            // Recopier les éléments
            foreach ($proforma->ElementFactureProformas as $el) {
                ElementBordereauLivraison::create([
                    'bordereau_livraison_id' => $bordereau->id,
                    'produits_id'            => $el->produits_id,
                    'designation'            => $el->designation,
                    'quantite'               => $el->quantite,
                    'prix_unitaire'          => $el->prix_unitaire,
                    'montant_total'          => $el->montant_total,
                ]);
            }

            // Verrouiller la proforma
            $proforma->update([
                'statut'                 => 'transformée',
                'bordereau_livraison_id' => $bordereau->id,
            ]);
        });

        smilify('success', 'Facture proforma transformée en bordereau de livraison avec succès.');
        return redirect()->route('module_bordereau_livraison.show', $bordereau->id);
    }

    /* ============================================================
     |  TRANSFORMATION : Bordereau -> Facture définitive
     * ============================================================ */
    public function transformToFacture($id)
    {
        $bordereau = BordereauLivraison::with('ElementBordereauLivraisons')->findOrFail($id);

        if ($bordereau->statut === 'facturé' || $bordereau->facture_id) {
            smilify('error', 'Ce bordereau de livraison a déjà été facturé.');
            return redirect()->back();
        }

        $facture = null;

        DB::transaction(function () use ($bordereau, &$facture) {
            $year = date('Y');
            $last = Facture::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
            $next = ($last ? intval(substr($last->code, 4, 5)) : 0) + 1;
            $code = 'FAC-' . str_pad($next, 5, '0', STR_PAD_LEFT) . '/' . $year;

            $facture = Facture::create([
                'code'                   => $code,
                'reference'              => $bordereau->reference,
                'date'                   => now()->toDateString(),
                'statut'                 => 'en attente',
                'facture_proforma_id'    => $bordereau->facture_proforma_id,
                'bordereau_livraison_id' => $bordereau->id,
                'contribuable_id'        => $bordereau->contribuables_id,
                'service_id'             => $bordereau->service_id,
                'objet'                  => $bordereau->objet,
                'remise_1'               => $bordereau->remise_1 ?? 0,
                'remise_2'               => $bordereau->remise_2 ?? 0,
            ]);

            foreach ($bordereau->ElementBordereauLivraisons as $el) {
                ElementFacture::create([
                    'facture_id'    => $facture->id,
                    'produits_id'   => $el->produits_id,
                    'designation'   => $el->designation,
                    'quantite'      => $el->quantite,
                    'prix_unitaire' => $el->prix_unitaire,
                    'montant_total' => $el->montant_total,
                ]);
            }

            $bordereau->update([
                'statut'     => 'facturé',
                'facture_id' => $facture->id,
            ]);
        });

        smilify('success', 'Bordereau de livraison transformé en facture définitive avec succès.');
        return redirect()->route('module_ordre_facture.show', $facture->id);
    }

    /* ============================================================
     |  AFFICHAGE
     * ============================================================ */
    public function show($id)
    {
        $bordereau = BordereauLivraison::with([
            'Contribuable', 'Service', 'Categorie', 'User',
            'FactureProforma', 'Facture'
        ])->findOrFail($id);

        $elements = ElementBordereauLivraison::with('Produit')
            ->where('bordereau_livraison_id', $bordereau->id)
            ->get();

        $montant_total = $elements->sum(function ($el) {
            return $el->montant_total ?? ($el->quantite * $el->prix_unitaire);
        });

        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits      = Produit::orderBy('code')->get();

        return view('pages.bordereau_livraison.show', compact(
            'bordereau', 'elements', 'montant_total', 'contribuables', 'produits'
        ));
    }

    /* ============================================================
     |  MISE À JOUR (uniquement si non facturé)
     * ============================================================ */
    public function update(Request $request, $id)
    {
        $bordereau = BordereauLivraison::findOrFail($id);

        if ($bordereau->statut === 'facturé') {
            smilify('error', 'Ce bordereau a déjà été facturé, modification impossible.');
            return redirect()->back();
        }

        DB::transaction(function () use ($request, $bordereau) {
            $bordereau->update([
                'reference'        => $request->reference ?? $bordereau->reference,
                'date'             => $request->date ?? $bordereau->date,
                'statut'           => $request->statut ?? $bordereau->statut,
                'transporteur'     => $request->transporteur,
                'livreur'          => $request->livreur,
                'observation'      => $request->observation,
                'contribuables_id' => $request->contribuables_id ?? $bordereau->contribuables_id,
                'service_id'       => $request->service_id ?? $bordereau->service_id,
                'objet'            => $request->objet ?? $bordereau->objet,
                'remise_1'         => $request->remise_1 ?? $bordereau->remise_1,
                'remise_2'         => $request->remise_2 ?? $bordereau->remise_2,
            ]);

            $this->syncElements($bordereau, $request);
        });

        smilify('success', 'Bordereau de livraison mis à jour avec succès.');
        return redirect()->route('module_bordereau_livraison.show', $bordereau->id);
    }

    /* ============================================================
     |  CHANGEMENT D'ÉTAT : marquer comme livré
     * ============================================================ */
    public function markAsDelivered($id)
    {
        $bordereau = BordereauLivraison::findOrFail($id);

        if ($bordereau->statut === 'facturé') {
            smilify('error', 'Bordereau déjà facturé.');
            return redirect()->back();
        }

        $bordereau->update(['statut' => 'livré']);
        smilify('success', 'Bordereau marqué comme livré.');
        return redirect()->back();
    }

    /* ============================================================
     |  SUPPRESSION (seulement si non facturé)
     * ============================================================ */
    public function destroy($id)
    {
        $bordereau = BordereauLivraison::findOrFail($id);

        if ($bordereau->statut === 'facturé') {
            smilify('error', 'Impossible de supprimer un bordereau déjà facturé.');
            return redirect()->back();
        }

        // Réouvrir la proforma source si applicable
        if ($bordereau->facture_proforma_id) {
            FactureProforma::where('id', $bordereau->facture_proforma_id)->update([
                'statut'                 => 'en attente',
                'bordereau_livraison_id' => null,
            ]);
        }

        ElementBordereauLivraison::where('bordereau_livraison_id', $bordereau->id)->delete();
        $bordereau->delete();

        smilify('success', 'Bordereau de livraison supprimé avec succès.');
        return redirect()->route('module_bordereau_livraison.index');
    }

    /* ============================================================
     |  IMPRESSION
     * ============================================================ */
    public function print($id)
    {
        $bordereau = BordereauLivraison::with([
            'Contribuable.Categorie', 'Service', 'Categorie', 'User'
        ])->findOrFail($id);

        $elements = ElementBordereauLivraison::with(['Produit.tva'])
            ->where('bordereau_livraison_id', $bordereau->id)
            ->get();

        $entete = Entete::first();

        // Totaux
        $total_ht = 0;
        $total_remise = 0;
        $tva_groups = [];
        $bic_groups = [];

        foreach ($elements as $el) {
            $quantite = (float) $el->quantite;
            $prix     = (float) $el->prix_unitaire;
            $remise   = (float) ($el->remise ?? 0);

            $brut       = $quantite * $prix;
            $montantHt  = $brut - ($brut * $remise / 100);
            $tauxTva    = (float) optional(optional($el->Produit)->tva)->taux;
            $taxe       = $montantHt * $tauxTva / 100;
            $ligneTtc   = $montantHt + $taxe;

            $total_ht     += $montantHt;
            $total_remise += $brut * $remise / 100;

            $key = (string) $tauxTva;
            if (!isset($tva_groups[$key])) {
                $tva_groups[$key] = ['taux' => $tauxTva, 'base' => 0, 'taxe' => 0];
            }
            $tva_groups[$key]['base'] += $montantHt;
            $tva_groups[$key]['taxe'] += $taxe;

            $tauxBic = (float) optional(optional($el->Produit)->bic)->taux;
            if ($tauxBic > 0) {
                $bic = $ligneTtc * $tauxBic / 100;
                $bicKey = (string) $tauxBic;
                if (!isset($bic_groups[$bicKey])) {
                    $bic_groups[$bicKey] = ['taux' => $tauxBic, 'base_ttc' => 0, 'montant' => 0];
                }
                $bic_groups[$bicKey]['base_ttc'] += $ligneTtc;
                $bic_groups[$bicKey]['montant']  += $bic;
            }
        }

        $total_taxe  = array_sum(array_column($tva_groups, 'taxe'));
        $total_ttc   = $total_ht + $total_taxe;
        $total_bic   = array_sum(array_column($bic_groups, 'montant'));
        $remise_1    = (float) ($bordereau->remise_1 ?? 0);
        $remise_2    = (float) ($bordereau->remise_2 ?? 0);
        $net_a_payer = max(0, $total_ttc + $total_bic - $remise_1 - $remise_2);

        // Compatibilité avec le template existant (variable $facture)
        $facture = $bordereau;

        return view('pages.bordereau_livraison.print', compact(
            'bordereau', 'facture', 'elements', 'entete',
            'total_ht', 'total_remise', 'tva_groups', 'bic_groups',
            'total_taxe', 'total_ttc', 'total_bic', 'remise_1', 'remise_2', 'net_a_payer'
        ));
    }

    /* ============================================================
     |  HELPERS
     * ============================================================ */
    protected function generateCode(): string
    {
        $year = date('Y');
        $last = BordereauLivraison::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $next = ($last ? intval(substr($last->code, 3, 5)) : 0) + 1;
        return 'BL-' . str_pad($next, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }

    protected function saveElements(BordereauLivraison $bordereau, Request $request): void
    {
        $produits     = (array) $request->input('produits_id', []);
        $designations = (array) $request->input('designation', []);
        $quantites    = (array) $request->input('quantite', []);
        $prix         = (array) $request->input('prix_unitaire', []);
        $remises      = (array) $request->input('remise', []);

        $count = max(count($produits), count($quantites), count($prix));

        for ($i = 0; $i < $count; $i++) {
            $produitId = $produits[$i] ?? null;
            $quantite  = isset($quantites[$i]) ? (float) $quantites[$i] : 0;
            $prixU     = isset($prix[$i]) ? (float) $prix[$i] : 0;
            $remise    = isset($remises[$i]) ? (float) $remises[$i] : 0;

            if (!$produitId && $quantite == 0 && $prixU == 0) continue;

            $montant = $quantite * $prixU;
            if ($remise > 0) $montant -= $montant * $remise / 100;

            ElementBordereauLivraison::create([
                'bordereau_livraison_id' => $bordereau->id,
                'produits_id'            => $produitId,
                'designation'            => $designations[$i] ?? null,
                'quantite'               => $quantite,
                'prix_unitaire'          => $prixU,
                'remise'                 => $remise,
                'montant_total'          => $montant,
            ]);
        }
    }

    protected function syncElements(BordereauLivraison $bordereau, Request $request): void
    {
        $elementIds   = (array) $request->input('element_id', []);
        $produits     = (array) $request->input('produits_id', []);
        $designations = (array) $request->input('designation', []);
        $quantites    = (array) $request->input('quantite', []);
        $prix         = (array) $request->input('prix_unitaire', []);
        $remises      = (array) $request->input('remise', []);

        $keptIds = [];
        $count = max(count($produits), count($quantites), count($prix));

        for ($i = 0; $i < $count; $i++) {
            $produitId = $produits[$i] ?? null;
            $quantite  = isset($quantites[$i]) ? (float) $quantites[$i] : 0;
            $prixU     = isset($prix[$i]) ? (float) $prix[$i] : 0;
            $remise    = isset($remises[$i]) ? (float) $remises[$i] : 0;

            if (!$produitId && $quantite == 0 && $prixU == 0) continue;

            $montant = $quantite * $prixU;
            if ($remise > 0) $montant -= $montant * $remise / 100;

            $data = [
                'bordereau_livraison_id' => $bordereau->id,
                'produits_id'            => $produitId,
                'designation'            => $designations[$i] ?? null,
                'quantite'               => $quantite,
                'prix_unitaire'          => $prixU,
                'remise'                 => $remise,
                'montant_total'          => $montant,
            ];

            $existingId = $elementIds[$i] ?? null;
            if ($existingId) {
                $element = ElementBordereauLivraison::find($existingId);
                if ($element) {
                    $element->update($data);
                    $keptIds[] = $element->id;
                    continue;
                }
            }
            $element = ElementBordereauLivraison::create($data);
            $keptIds[] = $element->id;
        }

        ElementBordereauLivraison::where('bordereau_livraison_id', $bordereau->id)
            ->whereNotIn('id', $keptIds)
            ->delete();
    }
}
