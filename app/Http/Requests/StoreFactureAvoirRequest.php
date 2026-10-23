<?php

namespace App\Http\Requests;

use App\Models\Facture;

class StoreFactureAvoirRequest extends StoreFactureRequest
{
    public function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $nature = strtoupper((string) ($this->input('nature_avoir') ?: 'RAN'));
        $motifMap = [
            'RAN' => 'annulation',
            'COR' => 'erreur',
            'RAM' => 'retour',
            'RRR' => 'remise',
        ];
        $currentMotif = $this->input('motif');

        $produits = (array) $this->input('produits_id', []);
        $quantites = (array) $this->input('quantite', []);
        $prixUnitaires = (array) $this->input('prix_unitaire', []);

        $cleanProduits = [];
        $cleanQuantites = [];
        $cleanPrix = [];

        foreach ($produits as $idx => $pid) {
            $q = (float) ($quantites[$idx] ?? 0);
            $p = (float) ($prixUnitaires[$idx] ?? 0);
            if ($pid && $q > 0) {
                $cleanProduits[] = (int) $pid;
                $cleanQuantites[] = $q;
                $cleanPrix[] = $p;
            }
        }

        $mergeData = [
            'nature_avoir' => $nature,
            'motif' => $currentMotif ?: ($motifMap[$nature] ?? 'annulation'),
        ];

        if (!empty($cleanProduits)) {
            $mergeData['produits_id'] = $cleanProduits;
            $mergeData['quantite'] = $cleanQuantites;
            $mergeData['prix_unitaire'] = $cleanPrix;
        }

        $this->merge($mergeData);
    }

    public function rules(): array
    {
        return [
            'contribuables_id' => 'required|integer|exists:contribuables,id',
            'facture_id' => [
                'required',
                'integer',
                'exists:factures,id',
                function ($attribute, $value, $fail) {
                    $facture = Facture::find($value);
                    if (!$facture) {
                        return;
                    }
                    if ($facture->aAvoirTotal() || $facture->soldeAvoirDisponible() <= 0.009) {
                        $fail("La facture {$facture->code} a déjà fait l'objet d'une annulation totale ou son solde d'avoir disponible est épuisé.");
                    }
                },
            ],
            'nature_avoir' => [
                'required',
                'in:COR,RAN,RAM,RRR',
                function ($attribute, $value, $fail) {
                    $nature = strtoupper((string) $value);
                    $factureId = $this->input('facture_id');
                    $facture = Facture::find($factureId);
                    if ($facture && $nature === 'RAN' && $facture->aSubiAvoir()) {
                        $fail("La facture {$facture->code} a déjà fait l'objet d'un avoir partiel. L'annulation totale (RAN) est interdite. Veuillez choisir un retour de marchandises (RAM) ou une remise (RRR).");
                    }
                },
            ],
            'motif' => 'nullable|string|max:255',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'remise_1' => 'nullable|numeric|min:0',
            'remise_2' => 'nullable|numeric|min:0',
            'produits_id' => 'required|array|min:1',
            'produits_id.*' => 'required|integer|distinct|exists:produits,id',
            'quantite' => 'required|array',
            'quantite.*' => 'required|numeric|gt:0',
            'prix_unitaire.*' => 'required|numeric|gt:0',
            'effectuer_remboursement' => 'nullable',
            'remboursement_mode' => 'nullable|string|max:50',
            'remboursement_montant' => 'nullable|numeric|min:0',
            'remboursement_ref' => 'nullable|string|max:150',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $facture = Facture::with('ElementFactures')->find($this->input('facture_id'));
            if (!$facture) {
                return;
            }

            $produits = (array) $this->input('produits_id', []);
            $quantites = (array) $this->input('quantite', []);

            foreach ($produits as $idx => $pid) {
                $pid = (int) $pid;
                $qteSaisie = (float) ($quantites[$idx] ?? 0);
                $elOrig = $facture->ElementFactures->where('produits_id', $pid)->first();
                if (!$elOrig) {
                    $validator->errors()->add("produits_id.{$idx}", "Le produit sélectionné n'appartient pas à la facture d'origine.");
                    continue;
                }
                $qteRestante = $facture->quantiteRestanteProduit($pid, (float) $elOrig->quantite);
                if ($qteSaisie > ($qteRestante + 0.009)) {
                    $nomProd = $elOrig->designation ?: "Produit #{$pid}";
                    $validator->errors()->add("quantite.{$idx}", "La quantité demandée ({$qteSaisie}) pour '{$nomProd}' dépasse la quantité disponible restante ({$qteRestante}).");
                }
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'contribuables_id.required' => 'Sélectionnez le client de l’avoir.',
            'facture_id.required' => 'Sélectionnez la facture d’origine.',
            'facture_id.exists' => 'La facture d’origine sélectionnée n’existe plus.',
            'nature_avoir.required' => 'Sélectionnez le type d’avoir (Nature DGI).',
            'nature_avoir.in' => 'Le type d’avoir DGI doit être COR, RAN, RAM ou RRR.',
        ]);
    }
}
