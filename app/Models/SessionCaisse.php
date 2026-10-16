<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionCaisse extends Model
{
    use HasFactory;

    public const STATUT_OUVERTE = 'ouverte';
    public const STATUT_EN_ARRET = 'en_arret';
    public const STATUT_FERMEE = 'fermee';

    protected $table = 'session_caisses';

    protected $guarded = [];

    protected $casts = [
        'date_metier' => 'date:Y-m-d',
        'fond_initial' => 'float',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'fermeture_detail_modes' => 'array',
        'fermeture_detail_articles' => 'array',
        'fermeture_montant_ventes_ttc' => 'float',
        'fermeture_montant_annulations' => 'float',
        'fermeture_fond_theorique' => 'float',
        'montant_fermeture_declare' => 'float',
        'fermeture_ecart' => 'float',
    ];

    public function Caisse()
    {
        return $this->belongsTo(Caisse::class, 'caisse_id');
    }

    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function OpenedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function ClosedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function Arrets()
    {
        return $this->hasMany(ArretCaisse::class, 'session_caisse_id');
    }

    public function Tickets()
    {
        return $this->hasMany(TicketComptoir::class, 'session_caisse_id');
    }

    public function isOuverte(): bool
    {
        return $this->statut === self::STATUT_OUVERTE;
    }

    public function isEnArret(): bool
    {
        return $this->statut === self::STATUT_EN_ARRET;
    }

    public function isFermee(): bool
    {
        return $this->statut === self::STATUT_FERMEE;
    }

    public function allowsSales(): bool
    {
        return $this->statut === self::STATUT_OUVERTE;
    }
}
