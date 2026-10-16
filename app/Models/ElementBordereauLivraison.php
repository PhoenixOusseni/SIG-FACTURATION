<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementBordereauLivraison extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function BordereauLivraison()
    {
        return $this->belongsTo(BordereauLivraison::class, 'bordereau_livraison_id');
    }

    public function Produit()
    {
        return $this->belongsTo(Produit::class, 'produits_id');
    }
}
