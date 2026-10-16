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
}
