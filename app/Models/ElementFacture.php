<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementFacture extends Model
{
    use HasFactory;

    protected $guarded = [

    ];

    public function Facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    public function Produit()
    {
        return $this->belongsTo(Produit::class, 'produits_id');
    }

    /**
     * Montant calculé de la remise spécifique à cette ligne.
     */
    public function getCalculatedRemiseAttribute(): float
    {
        $qte = (float) ($this->quantite ?? 0);
        $pu = (float) ($this->prix_unitaire ?? 0);
        $brut = $qte * $pu;
        $valeur = (float) ($this->remise_valeur ?? 0);
        $type = strtoupper((string) ($this->remise_type ?? 'POURCENTAGE'));

        if ($valeur <= 0 || $brut <= 0) {
            return 0.0;
        }

        if ($type === 'MONTANT') {
            return round(min($brut, $valeur), 2);
        }

        return round($brut * (min(100, $valeur) / 100.0), 2);
    }

    /**
     * Montant net HT de la ligne (après déduction de la remise propre à la ligne).
     */
    public function getNetHtAttribute(): float
    {
        $qte = (float) ($this->quantite ?? 0);
        $pu = (float) ($this->prix_unitaire ?? 0);
        return max(0, round(($qte * $pu) - $this->calculated_remise, 2));
    }

    /**
     * Taxe spécifique unitaire (TS/u).
     */
    public function getTsUnitAttribute(): float
    {
        $prod = $this->Produit;
        if (!$prod) return 0.0;
        $pu = (float) ($this->prix_unitaire ?? 0);
        if ($prod->type_ts === 'POURCENTAGE' && (float) ($prod->pourcentage_ts ?? 0) > 0) {
            return round($pu * ((float) $prod->pourcentage_ts / 100.0), 2);
        }
        return (float) ($prod->montant_ts ?? 0);
    }

    /**
     * Taxe spécifique totale de la ligne (TS/u * Qté).
     */
    public function getTsLigneAttribute(): float
    {
        return round($this->ts_unit * (float) ($this->quantite ?? 0), 2);
    }

    /**
     * Base taxable légale de la ligne (Net HT + Taxe Spécifique).
     */
    public function getBaseImposableLigneAttribute(): float
    {
        return round($this->net_ht + $this->ts_ligne, 2);
    }
}
