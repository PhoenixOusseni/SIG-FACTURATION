<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglementFournisseur extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function FactureFournisseur()
    {
        return $this->belongsTo(FactureFournisseur::class, 'facture_fournisseur_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
