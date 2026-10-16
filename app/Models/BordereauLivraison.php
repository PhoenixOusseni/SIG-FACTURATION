<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BordereauLivraison extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function FactureProforma()
    {
        return $this->belongsTo(FactureProforma::class, 'facture_proforma_id');
    }

    public function Facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    public function ElementBordereauLivraisons()
    {
        return $this->hasMany(ElementBordereauLivraison::class);
    }

    /**
     * Montant total TTC + BIC du bordereau (somme des éléments).
     */
    public function getMontantTotalAttribute(): float
    {
        $total = 0.0;
        foreach ($this->ElementBordereauLivraisons()->with('Produit.tva')->get() as $el) {
            $brut      = (float) $el->quantite * (float) $el->prix_unitaire;
            $montantHt = $brut - ($brut * (float) ($el->remise ?? 0) / 100);
            $tauxTva   = (float) optional(optional($el->Produit)->tva)->taux;
            $ttc       = $montantHt + ($montantHt * $tauxTva / 100);
            $tauxBic   = (float) optional(optional($el->Produit)->bic)->taux;
            $bic       = $ttc * $tauxBic / 100;
            $total    += $ttc + $bic;
        }
        return round($total, 2);
    }

    /**
     * Net à payer estimé = Total TTC+BIC − remise_1 − remise_2.
     */
    public function getNetAPayerAttribute(): float
    {
        $remise = (float) ($this->remise_1 ?? 0) + (float) ($this->remise_2 ?? 0);
        return round(max(0, $this->montant_total - $remise), 2);
    }
}
