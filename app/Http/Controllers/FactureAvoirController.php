<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFactureAvoirRequest;
use App\Models\FactureAvoir;
use App\Models\ElementFactureAvoir;
use App\Models\Facture;
use App\Models\Contribuable;
use App\Models\Entete;
use App\Models\Produit;
use App\Services\SFE\InvoiceSigningService;
use App\Services\SFE\SfeInvoiceType;
use App\Services\SFE\StatusSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FactureAvoirController extends Controller
{
    protected $sfeSigner;
    protected $statusSnapshots;

    public function __construct(InvoiceSigningService $sfeSigner, StatusSnapshotService $statusSnapshots)
    {
        $this->sfeSigner = $sfeSigner;
        $this->statusSnapshots = $statusSnapshots;
    }

    /**
     * Liste des factures d'avoir de l'utilisateur connecté.
     */
    public function index()
    {
        $collection = FactureAvoir::with(['Contribuable', 'Facture', 'User'])
            ->where('users_id', auth()->id())
            ->orderByDesc('id')
            ->get();

        $factures = Facture::with(['Contribuable', 'ElementFactures.Produit'])
            ->where('statut', '!=', 'annulée')
            ->orderByDesc('id')
            ->get();
        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();
        $produitsData = $produits->keyBy('id')->map(function ($produit) {
            return [
                'code' => $produit->code,
                'designation' => $produit->designation,
                'prix_unitaire' => $produit->prix_unitaire,
                'qte' => $produit->qte,
            ];
        });

        // Préparer les données factures pour auto-remplissage
        $facturesData = [];
        foreach ($factures as $f) {
            $facturesData[$f->id] = [
                'code' => $f->code,
                'client_id' => $f->Contribuable?->id,
                'client_name' => $f->Contribuable?->assujeti,
                'objet' => $f->objet,
                'reference' => $f->reference,
                'remise_1' => $f->remise_1,
                'remise_2' => $f->remise_2,
                'lignes' => $f->ElementFactures->map(function ($element) {
                    return [
                        'produit_id' => $element->produits_id,
                        'quantite' => $element->quantite,
                        'prix_unitaire' => $element->prix_unitaire,
                    ];
                })->values(),
            ];
        }
        $facturesDataJson = json_encode($facturesData);
        $sourceFactureId = (string) request('facture_id', '');
        $sfeState = $this->statusSnapshots->current();

        $categories = \App\Models\Categorie::orderBy('code')->get();
        $tvas = \App\Models\Tva::orderBy('code')->get();
        $bics = \App\Models\Bic::orderBy('code')->get();

        return view('pages.facture_avoir.index', compact(
            'collection',
            'factures',
            'contribuables',
            'produits',
            'produitsData',
            'facturesDataJson',
            'sourceFactureId',
            'sfeState',
            'categories',
            'tvas',
            'bics'
        ));
    }

    /**
     * Liste de toutes les factures d'avoir.
     */
    public function all_facture()
    {
        $collection = FactureAvoir::with(['Contribuable', 'Facture', 'User'])
            ->orderByDesc('id')
            ->get();

        return view('pages.facture_avoir.liste', compact('collection'));
    }

    /**
     * Enregistrer une nouvelle facture d'avoir + ses éléments.
     */
    public function store(StoreFactureAvoirRequest $request)
    {
        $nature = SfeInvoiceType::normalizeNature($request->nature_avoir)
            ?: SfeInvoiceType::natureFromMotif($request->motif);

        $facture_avoir = DB::transaction(function () use ($request, $nature) {
            // Générer automatiquement le code de la facture d'avoir (AVR-00001/2026)
            $year = date('Y');
            $lastAvoir = FactureAvoir::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = ($lastAvoir ? intval(substr($lastAvoir->code, 4, 5)) : 0) + 1;
            $generatedCode = 'AVR-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            $facture_avoir = FactureAvoir::create([
                'code'             => $generatedCode,
                'reference'        => $request->reference,
                'date'             => $request->date ?? now()->format('Y-m-d'),
                'statut'           => $request->statut ?? 'en attente',
                'users_id'         => auth()->id(),
                'contribuables_id' => $request->contribuables_id,
                'facture_id'       => $request->facture_id,
                'objet'            => $request->objet,
                'motif'            => $request->motif,
                'nature_avoir'     => $nature,
                'remise_1'         => $request->remise_1 ?? 0,
                'remise_2'         => $request->remise_2 ?? 0,
            ]);

            $this->saveElements($facture_avoir, $request);

            return $facture_avoir;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'facture_avoir' => [
                    'id' => $facture_avoir->id,
                    'code' => $facture_avoir->code,
                    'statut' => $facture_avoir->statut,
                ],
            ]);
        }

        if (function_exists('smilify')) {
            smilify('success', 'Facture d\'avoir enregistrée avec succès.');
        }

        return redirect()->route('module_facture_avoir.index');
    }

    /**
     * Afficher une facture d'avoir.
     */
    public function show($id)
    {
        $facture_avoir = FactureAvoir::with(['Contribuable', 'Facture', 'User'])->findOrFail($id);
        $elements = ElementFactureAvoir::with(['Produit.tva'])
            ->where('facture_avoir_id', $facture_avoir->id)
            ->get();

        $montant_total = $elements->sum(function ($element) {
            return $element->montant_total ?? ($element->quantite * $element->prix_unitaire);
        });

        $factures = Facture::orderByDesc('id')->get();
        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();

        return view('pages.facture_avoir.show', compact('facture_avoir', 'elements', 'montant_total', 'factures', 'contribuables', 'produits'));
    }

    /**
     * Afficher le formulaire d'édition d'une facture d'avoir.
     */
    public function edit($id)
    {
        $facture_avoir = FactureAvoir::with(['Contribuable', 'Facture'])->findOrFail($id);
        if ($facture_avoir->is_fiscal_locked) {
            abort(423, 'Avoir fiscalisé : modification interdite.');
        }
        $elements = ElementFactureAvoir::with('Produit')
            ->where('facture_avoir_id', $facture_avoir->id)
            ->get();

        $factures = Facture::with('Contribuable')->orderByDesc('id')->get();
        $contribuables = Contribuable::orderBy('assujeti')->get();
        $produits = Produit::orderBy('code')->get();
        $natures = SfeInvoiceType::NATURES;

        // Préparer les données factures pour auto-remplissage
        $facturesData = [];
        foreach ($factures as $f) {
            $facturesData[$f->id] = [
                'code' => $f->code,
                'client_id' => $f->Contribuable?->id,
                'client_name' => $f->Contribuable?->assujeti,
                'objet' => $f->objet,
            ];
        }
        $facturesDataJson = json_encode($facturesData);

        return view('pages.facture_avoir.edit', compact('facture_avoir', 'elements', 'factures', 'contribuables', 'produits', 'facturesDataJson', 'natures'));
    }

    /**
     * Mettre à jour la facture d'avoir + ses éléments.
     */
    public function update(Request $request, $id)
    {
        $facture_avoir = FactureAvoir::findOrFail($id);

        if ($facture_avoir->is_fiscal_locked) {
            abort(423, 'Avoir fiscalisé : toute modification est interdite.');
        }

        $request->validate([
            'motif' => 'nullable|in:retour,erreur,annulation,remise,produit_defectueux',
            'nature_avoir' => 'nullable|in:COR,RAN,RAM,RRR',
        ]);

        $nature = SfeInvoiceType::normalizeNature($request->nature_avoir)
            ?: SfeInvoiceType::natureFromMotif($request->motif)
            ?: $facture_avoir->nature_avoir;

        DB::transaction(function () use ($request, $facture_avoir, $nature) {
            $facture_avoir->update([
                'code'             => $request->code ?? $facture_avoir->code,
                'reference'        => $request->reference,
                'date'             => $request->date,
                'statut'           => $request->statut ?? $facture_avoir->statut,
                'objet'            => $request->objet,
                'motif'            => $request->motif,
                'nature_avoir'     => $nature,
                'remise_1'         => $request->remise_1 ?? 0,
                'remise_2'         => $request->remise_2 ?? 0,
                'facture_id'       => $request->facture_id,
                'contribuables_id' => $request->contribuables_id,
            ]);

            ElementFactureAvoir::where('facture_avoir_id', $facture_avoir->id)->delete();
            $this->saveElements($facture_avoir, $request);
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture d\'avoir mise à jour avec succès.');
        }

        return redirect()->route('module_facture_avoir.show', $facture_avoir->id);
    }

    /**
     * Supprimer une facture d'avoir.
     */
    public function destroy($id)
    {
        $facture_avoir = FactureAvoir::findOrFail($id);

        if ($facture_avoir->is_fiscal_locked) {
            if (function_exists('smilify')) {
                smilify('error', 'Avoir fiscalisé : suppression interdite.');
            }
            return redirect()->route('module_facture_avoir.show', $facture_avoir->id);
        }

        DB::transaction(function () use ($facture_avoir) {
            ElementFactureAvoir::where('facture_avoir_id', $facture_avoir->id)->delete();
            $facture_avoir->delete();
        });

        if (function_exists('smilify')) {
            smilify('success', 'Facture d\'avoir supprimée.');
        }

        return redirect()->route('module_facture_avoir.index');
    }

    /**
     * Fiscaliser l'avoir via le bridge SFE (FA / EA).
     */
    public function certifier($id)
    {
        $facture_avoir = FactureAvoir::findOrFail($id);

        try {
            $this->sfeSigner->signAvoir($facture_avoir->fresh());
            if (function_exists('smilify')) {
                smilify('success', 'Avoir fiscalisé et verrouillé avec succès.');
            }
        } catch (\Throwable $e) {
            report($e);
            Log::warning('Fiscalisation avoir refusée', [
                'facture_avoir_id' => $facture_avoir->id,
                'message' => $e->getMessage(),
            ]);
            if (function_exists('smilify')) {
                smilify('error', 'Fiscalisation refusée : ' . $e->getMessage());
            }
        }

        return redirect()->route('module_facture_avoir.show', $facture_avoir->id);
    }

    /**
     * Sauvegarder les éléments de la facture d'avoir.
     */
    private function saveElements($facture_avoir, $request)
    {
        $produitIds = array_filter((array) $request->input('produits_id', []));
        $quantites = (array) $request->input('quantite', []);
        $prixUnitaires = (array) $request->input('prix_unitaire', []);

        foreach ($produitIds as $index => $produitId) {
            $qte = (float) ($quantites[$index] ?? 0);
            $prix = (float) ($prixUnitaires[$index] ?? 0);
            $montantTotal = $qte * $prix;

            ElementFactureAvoir::create([
                'facture_avoir_id' => $facture_avoir->id,
                'produits_id'      => $produitId,
                'quantite'         => $qte,
                'prix_unitaire'    => $prix,
                'montant_total'    => $montantTotal,
            ]);
        }
    }

    /**
     * Imprimer une facture d'avoir.
     */
    public function print($id)
    {
        $facture_avoir = FactureAvoir::with(['Contribuable.Categorie', 'Facture', 'User'])->findOrFail($id);
        $elements = ElementFactureAvoir::with(['Produit.tva'])
            ->where('facture_avoir_id', $facture_avoir->id)
            ->get();
        $entete = Entete::first();

        // Mêmes règles de calcul que la facture définitive (cf. FactureController::print).
        $total_ht   = 0.0;
        $tva_groups = []; // [taux => ['taux','base','taxe']]
        $bic_groups = []; // [taux => ['taux','base_ttc','montant']]

        foreach ($elements as $element) {
            $quantite  = (float) $element->quantite;
            $prix      = (float) $element->prix_unitaire;
            $montantHt = $quantite * $prix;

            $total_ht += $montantHt;

            $tauxTva = (float) optional(optional($element->Produit)->tva)->taux;
            $keyTva  = (string) $tauxTva;
            if (!isset($tva_groups[$keyTva])) {
                $tva_groups[$keyTva] = ['taux' => $tauxTva, 'base' => 0.0, 'taxe' => 0.0];
            }
        }

        // Récupérer le taux BIC de la facture originale (une seule fois)
        $tauxBic = (float) optional(optional($facture_avoir->Facture)->Bic)->taux;
        if ($tauxBic > 0) {
            $keyBic = (string) $tauxBic;
            $bic_groups[$keyBic] = ['taux' => $tauxBic, 'base_ttc' => 0.0, 'montant' => 0.0];
        }

        $remise_1       = (float) ($facture_avoir->remise_1 ?? 0);
        $remise_2       = (float) ($facture_avoir->remise_2 ?? 0);
        $net_commercial = max(0, $total_ht - $remise_1 - $remise_2);

        foreach ($tva_groups as $key => $g) {
            $tva_groups[$key]['base'] = $net_commercial;
            $tva_groups[$key]['taxe'] = $net_commercial * $g['taux'] / 100;
        }
        $total_taxe = array_sum(array_column($tva_groups, 'taxe'));
        $total_ttc  = $net_commercial + $total_taxe;

        foreach ($bic_groups as $key => $g) {
            $bic_groups[$key]['base_ttc'] = $total_ttc;
            $bic_groups[$key]['montant']  = $total_ttc * $g['taux'] / 100;
        }
        $total_bic = array_sum(array_column($bic_groups, 'montant'));

        $montant_total = $total_ttc + $total_bic;

        // Certification FA/EA de l'avoir (pas celle de l'original seule).
        $certif = $facture_avoir->certification_active;
        $certifOriginal = optional($facture_avoir->Facture)->certification_active;
        $natureLabel = SfeInvoiceType::NATURES[$facture_avoir->nature_avoir ?? '']
            ?? ($facture_avoir->nature_avoir ?: null);
        $isExportAvoir = $certif && $certif->type_facture === 'EA';

        return view('pages.facture_avoir.print', compact(
            'facture_avoir', 'elements', 'entete', 'certif', 'certifOriginal',
            'natureLabel', 'isExportAvoir',
            'total_ht', 'remise_1', 'remise_2', 'net_commercial',
            'tva_groups', 'total_taxe',
            'bic_groups', 'total_bic',
            'total_ttc', 'montant_total'
        ));
    }
}
