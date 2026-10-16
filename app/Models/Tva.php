<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tva extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'intitule',
        'compte_tva',
        'taux',
    ];

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }
}
