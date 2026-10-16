<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArretCaisse extends Model
{
    use HasFactory;

    protected $table = 'arret_caisses';

    protected $guarded = [];

    protected $casts = [
        'arrete_at' => 'datetime',
        'montant_ventes_ttc' => 'float',
        'montant_annulations' => 'float',
        'fond_theorique' => 'float',
        'fond_declare' => 'float',
        'ecart' => 'float',
        'detail_modes' => 'array',
        'detail_articles' => 'array',
    ];

    public function SessionCaisse()
    {
        return $this->belongsTo(SessionCaisse::class, 'session_caisse_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
