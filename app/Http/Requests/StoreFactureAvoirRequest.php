<?php

namespace App\Http\Requests;

class StoreFactureAvoirRequest extends StoreFactureRequest
{
    public function rules(): array
    {
        return [
            'contribuables_id' => 'required|integer|exists:contribuables,id',
            'facture_id' => 'required|integer|exists:factures,id',
            'motif' => 'required|in:retour,erreur,annulation,remise,produit_defectueux',
            'nature_avoir' => 'required|in:COR,RAN,RAM,RRR',
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
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'contribuables_id.required' => 'Sélectionnez le client de l’avoir.',
            'facture_id.required' => 'Sélectionnez la facture d’origine.',
            'facture_id.exists' => 'La facture d’origine sélectionnée n’existe plus.',
            'motif.required' => 'Sélectionnez le motif de l’avoir.',
            'motif.in' => 'Le motif de l’avoir est invalide.',
            'nature_avoir.required' => 'Sélectionnez la nature DGI de l’avoir.',
            'nature_avoir.in' => 'La nature DGI doit être COR, RAN, RAM ou RRR.',
        ]);
    }
}
