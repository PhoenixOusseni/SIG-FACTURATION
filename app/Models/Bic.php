<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bic extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'intitule',
        'taux',
    ];

    public function Factures()
    {
        return $this->hasMany(Facture::class, 'bic_id');
    }
}
