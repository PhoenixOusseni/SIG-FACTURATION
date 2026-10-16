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
}
