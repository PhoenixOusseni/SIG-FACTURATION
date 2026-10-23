<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureDuplicata extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date_certification' => 'datetime',
        'vendeur_json'       => 'array',
        'client_json'        => 'array',
        'lignes_json'        => 'array',
        'paiements_json'     => 'array',
        'totaux_json'        => 'array',
        'dgi_json'           => 'array',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
}
