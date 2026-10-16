<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglementTicket extends Model
{
    use HasFactory;

    protected $table = 'reglement_tickets';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'montant' => 'float',
        'montant_recu' => 'float',
        'rendu' => 'float',
    ];

    public function Ticket()
    {
        return $this->belongsTo(TicketComptoir::class, 'ticket_comptoir_id');
    }

    public function ModePaie()
    {
        return $this->belongsTo(ModePaie::class, 'modepaie_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function isEspeces(): bool
    {
        $mode = $this->relationLoaded('ModePaie')
            ? $this->getRelation('ModePaie')
            : $this->ModePaie;

        return $mode && strtoupper((string) $mode->code) === 'ESP';
    }
}
