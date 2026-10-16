<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Typefacture extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Relation : Un type de facture peut avoir plusieurs factures
     */
    public function Factures()
    {
        return $this->hasMany(Facture::class, 'typefacture_id');
    }
}
