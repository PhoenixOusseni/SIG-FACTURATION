<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureAvoir extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
        'sfe_locked_at' => 'datetime',
    ];

    public function getIsFiscalLockedAttribute(): bool
    {
        return !is_null($this->sfe_locked_at) || !is_null($this->certification_active);
    }

    public function Facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    public function Contribuable()
    {
        return $this->belongsTo(Contribuable::class, 'contribuables_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function ModePaie()
    {
        return $this->belongsTo(ModePaie::class, 'modepaie_id');
    }

    public function ElementFactureAvoirs()
    {
        return $this->hasMany(ElementFactureAvoir::class, 'facture_avoir_id');
    }

    public function McfCertifications()
    {
        return $this->hasMany(McfCertification::class, 'facture_avoir_id')->orderByDesc('id');
    }

    /**
     * Certification FA/EA active de cet avoir.
     */
    public function getCertificationActiveAttribute(): ?McfCertification
    {
        return $this->McfCertifications()
            ->where('statut', 'certifiee')
            ->whereIn('type_facture', ['FA', 'EA'])
            ->first();
    }

    /**
     * Montant total TTC + BIC approximatif (même logique que la facture vente).
     */
    public function getMontantTotalAttribute(): float
    {
        $total = 0.0;
        $tauxBic = (float) optional(optional($this->Facture)->Bic)->taux;

        foreach ($this->ElementFactureAvoirs()->with('Produit.tva')->get() as $el) {
            $montantHt = (float) $el->quantite * (float) $el->prix_unitaire;
            $tauxTva = (float) optional(optional($el->Produit)->tva)->taux;
            $ttc = $montantHt + ($montantHt * $tauxTva / 100);
            $bic = $ttc * $tauxBic / 100;
            $total += $ttc + $bic;
        }

        $remise = (float) ($this->remise_1 ?? 0) + (float) ($this->remise_2 ?? 0);

        return round(max(0, $total - $remise), 2);
    }
}
