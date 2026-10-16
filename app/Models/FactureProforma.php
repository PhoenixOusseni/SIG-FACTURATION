<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureProforma extends Model
{
    use HasFactory;

    protected $guarded = [

    ];

    public function Contribuable()
    {
        return $this->belongsTo(Contribuable::class, 'contribuables_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function Categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    // method for ElementFactureProforma
    public function ElementFactureProformas()
    {
        return $this->hasMany(ElementFactureProforma::class);
    }

    public function BordereauLivraison()
    {
        return $this->belongsTo(BordereauLivraison::class, 'bordereau_livraison_id');
    }

    public function BordereauLivraisons()
    {
        return $this->hasMany(BordereauLivraison::class, 'facture_proforma_id');
    }

    /**
     * Montant total TTC + BIC de la facture proforma (somme des éléments).
     */
    public function getMontantTotalAttribute(): float
    {
        $total = 0.0;
        foreach ($this->ElementFactureProformas()->with('Produit.tva')->get() as $el) {
            $montantHt = (float) $el->quantite * (float) $el->prix_unitaire;
            $tauxTva   = (float) optional(optional($el->Produit)->tva)->taux;
            $ttc       = $montantHt + ($montantHt * $tauxTva / 100);
            $tauxBic   = (float) optional(optional($el->Produit)->bic)->taux;
            $bic       = $ttc * $tauxBic / 100;
            $total    += $ttc + $bic;
        }
        return round($total, 2);
    }

    /**
     * Net à payer estimé = Total TTC+BIC − Ristourne.
     */
    public function getNetAPayerAttribute(): float
    {
        $ristourne = (float) ($this->ristourne ?? 0);
        return round(max(0, $this->montant_total - $ristourne), 2);
    }
}
