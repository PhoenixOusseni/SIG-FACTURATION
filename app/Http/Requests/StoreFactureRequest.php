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

            $gross = 0.0;
            foreach ($products as $index => $product) {
                if ($product !== null && $product !== '') {
                    $gross += (float) ($quantities[$index] ?? 0) * (float) ($prices[$index] ?? 0);
                }
            }
            $discount = (float) $this->input('remise_1', 0) + (float) $this->input('remise_2', 0);
            if ($gross > 0 && $discount >= $gross) {
                $validator->errors()->add('remise_1', 'Le total des remises doit être inférieur au total brut de la facture.');
            }
        });
    }
}
