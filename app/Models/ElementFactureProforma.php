<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementFactureProforma extends Model
{
    use HasFactory;

    protected $guarded = [

    ];

    public function FactureProforma()
    {
        return $this->belongsTo(FactureProforma::class, 'facture_proforma_id');
    }

    public function Produit()
    {
        return $this->belongsTo(Produit::class, 'produits_id');
    }

    /**
     * Montant calculé de la remise spécifique à cette ligne de proforma.
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
     * Montant net HT de la ligne de proforma.
     */
    public function getNetHtAttribute(): float
    {
        $qte = (float) ($this->quantite ?? 0);
        $pu = (float) ($this->prix_unitaire ?? 0);
        return max(0, round(($qte * $pu) - $this->calculated_remise, 2));
    }
}
