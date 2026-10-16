<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entete extends Model
{
    use HasFactory;

    protected $guarded = [

    ];

    protected $casts = [
        'sfe_automatic_health_check' => 'boolean',
        'sfe_automatic_invoice_signing' => 'boolean',
    ];

    /**
     * L'entête est un singleton : il n'existe qu'un seul enregistrement.
     * Retourne l'entête existant ou en crée un vide si aucun n'existe.
     */
    public static function singleton(): self
    {
        return static::firstOrCreate([], ['denomination' => 'Ma société']);
    }
}
