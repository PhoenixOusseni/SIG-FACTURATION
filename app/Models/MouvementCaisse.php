<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementCaisse extends Model
{
    use HasFactory;

    public const TYPE_DEPOT = 'DEPOT';
    public const TYPE_RETRAIT = 'RETRAIT';

    protected $table = 'mouvements_caisse';
    protected $guarded = [];

    protected $casts = [
        'montant' => 'float',
        'date_mouvement' => 'datetime',
    ];

    public function Caisse()
    {
        return $this->belongsTo(Caisse::class, 'caisse_id');
    }

    public function SessionCaisse()
    {
        return $this->belongsTo(SessionCaisse::class, 'session_caisse_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isDepot(): bool
    {
        return $this->type === self::TYPE_DEPOT;
    }

    public function isRetrait(): bool
    {
        return $this->type === self::TYPE_RETRAIT;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->isDepot() ? 'Dépôt de numéraire (Entrée)' : 'Retrait de numéraire (Sortie)';
    }

    public function getTypeBadgeAttribute(): string
    {
        if ($this->isDepot()) {
            return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-arrow-down me-1"></i> Dépôt (Entrée)</span>';
        }
        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-arrow-up me-1"></i> Retrait (Sortie)</span>';
    }
}
