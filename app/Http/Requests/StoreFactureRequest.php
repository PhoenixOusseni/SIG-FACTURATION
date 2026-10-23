<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFactureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'contribuable_id' => 'required|integer|exists:contribuables,id',
            'typefacture_id' => 'required|integer|exists:typefactures,id',
            'modepaie_id' => 'required|integer|exists:modepaies,id',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'remise_1' => 'nullable|numeric|min:0',
            'remise_2' => 'nullable|numeric|min:0',
            'produits_id' => 'required|array|min:1',
            'produits_id.*' => 'required|integer|distinct|exists:produits,id',
            'quantite' => 'required|array',
            'quantite.*' => 'required|numeric|gt:0',
            'prix_unitaire' => 'required|array',
            'prix_unitaire.*' => 'required|numeric|gt:0',
            'designation' => 'nullable|array',
            'designation.*' => 'nullable|string|max:255',
            'facture_proforma_id' => 'nullable|integer',
            'mode_prix' => 'nullable|in:HT,TTC',
        ];
    }

    public function messages(): array
    {
        return [
            'contribuable_id.required' => 'Sélectionnez le client de la facture.',
            'contribuable_id.exists' => 'Le client sélectionné n’existe plus.',
            'typefacture_id.required' => 'Sélectionnez le type de facture.',
            'typefacture_id.exists' => 'Le type de facture sélectionné est invalide.',
            'modepaie_id.required' => 'Sélectionnez un mode de paiement.',
            'modepaie_id.exists' => 'Le mode de paiement sélectionné est invalide.',
            'date.required' => 'Renseignez la date de la facture.',
            'produits_id.required' => 'Ajoutez au moins un article à la facture.',
            'produits_id.*.required' => 'Sélectionnez un produit sur chaque ligne.',
            'produits_id.*.distinct' => 'Un produit apparaît plusieurs fois. Regroupez-le sur une seule ligne.',
            'produits_id.*.exists' => 'Un produit sélectionné n’existe plus.',
            'quantite.*.gt' => 'Chaque quantité doit être strictement supérieure à zéro.',
            'prix_unitaire.*.gt' => 'Chaque prix unitaire doit être strictement supérieur à zéro.',
            'remise_1.min' => 'La remise 1 ne peut pas être négative.',
            'remise_2.min' => 'La remise 2 ne peut pas être négative.',
        ];
    }

    public function prepareForValidation(): void
    {
        $remiseType = $this->input('remise_type', 'POURCENTAGE');
        $remiseVal = (float) $this->input('remise_valeur', $this->input('remise_1', 0));

        $totalBrutHt = 0.0;
        $qtes = (array) $this->input('quantite', []);
        $pus = (array) $this->input('prix_unitaire', []);
        foreach ($qtes as $i => $q) {
            $totalBrutHt += ((float) $q) * ((float) ($pus[$i] ?? 0));
        }

        if ($remiseType === 'POURCENTAGE') {
            $remiseMontant = round($totalBrutHt * (min(100, max(0, $remiseVal)) / 100), 2);
        } else {
            $remiseMontant = round(min($totalBrutHt, max(0, $remiseVal)), 2);
        }

        $this->merge([
            'remise_1' => $remiseMontant,
            'remise_2' => (float) $this->input('remise_2', 0),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $products = (array) $this->input('produits_id', []);
            $quantities = (array) $this->input('quantite', []);
            $prices = (array) $this->input('prix_unitaire', []);
            if (count($products) !== count($quantities) || count($products) !== count($prices)) {
                $validator->errors()->add('produits_id', 'Les lignes de facture sont incomplètes. Vérifiez produit, quantité et prix.');
                return;
            }

            $remiseType = $this->input('remise_type', 'POURCENTAGE');
            $remiseVal = (float) $this->input('remise_valeur', 0);
            if ($remiseType === 'POURCENTAGE' && $remiseVal > 100) {
                $validator->errors()->add('remise_valeur', 'Le taux de remise ne peut pas dépasser 100%.');
            }
        });
    }
}
