<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModePaie extends Model
{
    use HasFactory;

    protected $table = 'modepaies';
    protected $guarded = [];

    /**
     * Relation : Un mode de paiement peut avoir plusieurs factures
     */
    public function Factures()
    {
        return $this->hasMany(Facture::class, 'modepaie_id');
    }
}
