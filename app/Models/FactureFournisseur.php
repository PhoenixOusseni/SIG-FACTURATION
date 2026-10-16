<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureFournisseur extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function Fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseurs_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function ElementFactureFournisseurs()
    {
        return $this->hasMany(ElementFactureFournisseur::class, 'facture_fournisseurs_id');
    }

    public function ReglementFournisseurs()
    {
        return $this->hasMany(ReglementFournisseur::class, 'facture_fournisseur_id');
    }

    public function getMontantPayeAttribute(): float
    {
        if ($this->relationLoaded('ReglementFournisseurs')) {
            return (float) $this->getRelation('ReglementFournisseurs')->sum('montant');
        }

        return (float) $this->ReglementFournisseurs()->sum('montant');
    }

    public function getResteAPayerAttribute(): float
    {
        return round($this->montant_total - $this->montant_paye, 2);
    }

    public function getStatutPaiementAttribute(): string
    {
        $montantPaye = $this->montant_paye;
        $reste = round($this->montant_total - $montantPaye, 2);
        if ($montantPaye <= 0) {
            return 'impayée';
        }
        if ($reste <= 0.009) {
            return 'soldée';
        }
        return 'partielle';
    }

    /**
     * Montant total TTC (HT + TVA) de la facture fournisseur.
     */
    public function getMontantTotalAttribute(): float
    {
        $total = 0.0;
        $elements = $this->relationLoaded('ElementFactureFournisseurs')
            ? $this->getRelation('ElementFactureFournisseurs')
            : $this->ElementFactureFournisseurs()->with('Produit.tva')->get();

        foreach ($elements as $el) {
            $montantHt = (float) $el->quantite * (float) $el->prix_unitaire;
            $tauxTva   = (float) optional(optional($el->Produit)->tva)->taux;
            $total    += $montantHt + ($montantHt * $tauxTva / 100);
        }
        return round($total, 2);
    }
}
