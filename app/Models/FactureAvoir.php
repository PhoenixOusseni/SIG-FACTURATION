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
     * Montant total TTC + BIC de l'avoir (même logique fiscale que la facture vente).
     */
    public function getMontantTotalAttribute(): float
    {
        $total = 0.0;
        $isRrr = strtoupper((string) ($this->nature_avoir ?? '')) === 'RRR';
        $tauxBic = $isRrr ? 0.0 : (float) optional(optional($this->Facture)->Bic)->taux;
        $elements = $this->relationLoaded('ElementFactureAvoirs')
            ? $this->getRelation('ElementFactureAvoirs')
            : $this->ElementFactureAvoirs()->with('Produit.tva')->get();

        foreach ($elements as $el) {
            $quantite = (float) $el->quantite;
            $montantHt = $quantite * (float) $el->prix_unitaire;
            $prod = $el->Produit;
            $tsUnit = 0.0;
            if ($prod) {
                if ($prod->type_ts === 'POURCENTAGE' && (float) ($prod->pourcentage_ts ?? 0) > 0) {
                    $tsUnit = round(((float) $el->prix_unitaire) * ((float) $prod->pourcentage_ts / 100.0), 2);
                } elseif (!$isRrr) {
                    // Les TS fixes par unité ne s'appliquent PAS sur un avoir financier RRR (règle FEC/DGI)
                    $tsUnit = (float) ($prod->montant_ts ?? 0);
                }
            }
            $tsTotal = $tsUnit * $quantite;

            $tauxTva = (float) optional(optional($el->Produit)->tva)->taux;
            $baseTva = $montantHt + $tsTotal;
            $tva = $baseTva * $tauxTva / 100;
            $ttc = $baseTva + $tva;

            $bic = ($prod && $prod->isBien() && !$isRrr) ? ($ttc * $tauxBic / 100) : 0.0;
            $total += ($ttc + $bic);
        }

        $remise = (float) ($this->remise_1 ?? 0) + (float) ($this->remise_2 ?? 0);

        return round(max(0, $total - $remise), 2);
    }

    /**
     * Message d'erreur de certification éventuel.
     */
    public function getSfeErrorAttribute(): ?string
    {
        if (!empty($this->notes) && str_starts_with(trim($this->notes), '{')) {
            $data = json_decode($this->notes, true);
            return $data['sfe_error'] ?? null;
        }
        return null;
    }
}
