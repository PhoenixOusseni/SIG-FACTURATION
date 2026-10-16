<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use HasFactory;

    protected $fillable = [
        'motif',
        'date',
        'montant',
        'statut',
        'fournisseurs_id',
    ];

    protected $casts = [
        'date'    => 'date',
        'montant' => 'decimal:2',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseurs_id');
    }

    public function reglementDepenses()
    {
        return $this->hasMany(ReglementDepense::class, 'depense_id');
    }

    public function getMontantPayeAttribute(): float
    {
        if ($this->relationLoaded('reglementDepenses')) {
            return (float) $this->getRelation('reglementDepenses')->sum('montant');
        }

        return (float) $this->reglementDepenses()->sum('montant');
    }

    public function getResteAPayerAttribute(): float
    {
        return round((float) $this->montant - $this->montant_paye, 2);
    }

    public function getStatutPaiementAttribute(): string
    {
        $montantPaye = $this->montant_paye;
        if ($montantPaye <= 0) {
            return 'impayée';
        }
        if (((float) $this->montant - $montantPaye) <= 0.009) {
            return 'soldée';
        }
        return 'partielle';
    }
}
