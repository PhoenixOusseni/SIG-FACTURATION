<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function Categorie()
    {
        return $this->belongsTo(Categorie::class, 'categories_id');
    }

    public function FactureFournisseurs()
    {
        return $this->hasMany(FactureFournisseur::class, 'fournisseurs_id');
    }

    public function Depenses()
    {
        return $this->hasMany(Depense::class, 'fournisseurs_id');
    }
}
