<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComptoir extends Model
{
    use HasFactory;

    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_PAYE = 'paye';
    public const STATUT_ANNULE = 'annule';
    public const STATUT_CONVERTI = 'converti';

    protected $table = 'ticket_comptoirs';

    protected $guarded = [];

    protected $casts = [
        'date_metier' => 'date:Y-m-d',
        'remise' => 'float',
        'montant_ht' => 'float',
        'montant_ttc' => 'float',
        'stock_moved_at' => 'datetime',
        'annule_at' => 'datetime',
    ];

    public function SessionCaisse()
    {
        return $this->belongsTo(SessionCaisse::class, 'session_caisse_id');
    }

    public function Caisse()
    {
        return $this->belongsTo(Caisse::class, 'caisse_id');
    }

    public function Contribuable()
    {
        return $this->belongsTo(Contribuable::class, 'contribuable_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    public function Elements()
    {
        return $this->hasMany(ElementTicketComptoir::class, 'ticket_comptoir_id');
    }

    public function Reglements()
    {
        return $this->hasMany(ReglementTicket::class, 'ticket_comptoir_id');
    }

    public function isEditable(): bool
    {
        return in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_EN_ATTENTE], true);
    }

    public function isPaidOrConverted(): bool
    {
        return in_array($this->statut, [self::STATUT_PAYE, self::STATUT_CONVERTI], true);
    }
}
