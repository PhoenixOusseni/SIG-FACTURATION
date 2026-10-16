<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementFactureAvoir extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function FactureAvoir()
    {
        return $this->belongsTo(FactureAvoir::class, 'facture_avoir_id');
    }

    public function Produit()
    {
        return $this->belongsTo(Produit::class, 'produits_id');
    }
}
