<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function Sessions()
    {
        return $this->hasMany(SessionCaisse::class, 'caisse_id');
    }

    public function Tickets()
    {
        return $this->hasMany(TicketComptoir::class, 'caisse_id');
    }

    /**
     * Sanitize code for embedding in ticket FACT- codes (D2).
     * e.g. C01 stays C01; Caisse-01 → CAISSE01
     */
    public function codeToken(): string
    {
        $raw = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $this->code));

        return $raw !== '' ? $raw : 'C' . $this->id;
    }

    public function sessionOuverte(): ?SessionCaisse
    {
        return $this->Sessions()
            ->whereIn('statut', [SessionCaisse::STATUT_OUVERTE, SessionCaisse::STATUT_EN_ARRET])
            ->orderByDesc('id')
            ->first();
    }
}
