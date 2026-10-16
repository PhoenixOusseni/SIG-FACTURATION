<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementTicketComptoir extends Model
{
    use HasFactory;

    protected $table = 'element_ticket_comptoirs';

    protected $guarded = [];

    protected $casts = [
        'quantite' => 'float',
        'prix_unitaire' => 'float',
        'tva_taux' => 'float',
        'montant_ht' => 'float',
        'montant_tva' => 'float',
        'montant_ttc_ligne' => 'float',
    ];

    public function Ticket()
    {
        return $this->belongsTo(TicketComptoir::class, 'ticket_comptoir_id');
    }

    public function Produit()
    {
        return $this->belongsTo(Produit::class, 'produits_id');
    }

    /**
     * Compute line amounts from HT unit price (D13).
     */
    public static function computeLine(float $quantite, float $prixUnitaireHt, float $tvaTaux): array
    {
        $montantHt = round($quantite * $prixUnitaireHt, 2);
        $montantTva = round($montantHt * $tvaTaux / 100, 2);
        $montantTtc = round($montantHt + $montantTva, 2);

        return [
            'montant_ht' => $montantHt,
            'montant_tva' => $montantTva,
            'montant_ttc_ligne' => $montantTtc,
        ];
    }
}
