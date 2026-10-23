<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reglement extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function Facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * Génère un code unique de règlement au format REG-00001/2026.
     */
    public static function generateCode(): string
    {
        $year = date('Y');
        $last = self::whereYear('created_at', $year)->orderByDesc('id')->first();
        $next = ($last ? intval(substr($last->code, 4, 5)) : 0) + 1;
        return 'REG-' . str_pad($next, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }

    /**
     * Détermine si ce mouvement est un remboursement / décaissement de trésorerie.
     */
    public function isRemboursement(): bool
    {
        return (float) $this->montant < 0;
    }
}
