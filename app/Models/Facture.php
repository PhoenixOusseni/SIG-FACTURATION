<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $guarded = [

    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
        'sfe_locked_at' => 'datetime',
    ];

    public function getIsFiscalLockedAttribute(): bool
    {
        return !is_null($this->sfe_locked_at) || !is_null($this->certification_active);
    }

    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    
    public function Categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
    
    public function Contribuable()
    {
        return $this->belongsTo(Contribuable::class, 'contribuable_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // method for ElementFacture
    public function ElementFactures()
    {
        return $this->hasMany(ElementFacture::class);
    }

    public function FactureProforma()
    {
        return $this->belongsTo(FactureProforma::class, 'facture_proforma_id');
    }

    public function Typefacture()
    {
        return $this->belongsTo(Typefacture::class, 'typefacture_id');
    }

    public function ModePaie()
    {
        return $this->belongsTo(ModePaie::class, 'modepaie_id');
    }

    public function Bic()
    {
        return $this->belongsTo(Bic::class, 'bic_id');
    }

    public function BordereauLivraison()
    {
        return $this->belongsTo(BordereauLivraison::class, 'bordereau_livraison_id');
    }

    public function Reglements()
    {
        return $this->hasMany(Reglement::class, 'facture_id');
    }

    public function FactureAvoirs()
    {
        return $this->hasMany(FactureAvoir::class, 'facture_id');
    }

    public function McfCertifications()
    {
        return $this->hasMany(McfCertification::class)->orderByDesc('id');
    }

    /**
     * Dernière certification MCF active de type vente (FV/EV), s'il y en a une.
     * Les acomptes FT/ET sont mappés FV/EV côté bridge et verrouillent aussi la vente.
     */
    public function getCertificationActiveAttribute(): ?McfCertification
    {
        return $this->McfCertifications()
            ->where('statut', 'certifiee')
            ->whereIn('type_facture', ['FV', 'EV'])
            ->whereNull('facture_avoir_id')
            ->first();
    }

    /**
     * Code typefacture applicatif (FV, FT, EV, ET…).
     */
    public function getTypeCodeAttribute(): string
    {
        return strtoupper((string) (optional($this->Typefacture)->code ?: 'FV'));
    }

    /**
     * Montant total TTC + BIC de la facture (somme des éléments).
     */
    public function getMontantTotalAttribute(): float
    {
        $total = 0.0;
        $elements = $this->relationLoaded('ElementFactures')
            ? $this->getRelation('ElementFactures')
            : $this->ElementFactures()->with('Produit.tva')->get();

        foreach ($elements as $el) {
            $montantHt = (float) $el->quantite * (float) $el->prix_unitaire;
            $tauxTva   = (float) optional(optional($el->Produit)->tva)->taux;
            $ttc       = $montantHt + ($montantHt * $tauxTva / 100);
            $tauxBic   = (float) optional($this->Bic)->taux;
            $bic       = $ttc * $tauxBic / 100;
            $total    += $ttc + $bic;
        }
        return round($total, 2);
    }

    /**
     * Cumul des règlements enregistrés.
     */
    public function getMontantPayeAttribute(): float
    {
        if ($this->relationLoaded('Reglements')) {
            return (float) $this->getRelation('Reglements')->sum('montant');
        }

        return (float) $this->Reglements()->sum('montant');
    }

    /**
     * Solde restant (Total TTC + BIC − Remise 1 − Remise 2 − Ristourne − Règlements).
     */
    public function getResteAPayerAttribute(): float
    {
        $remise_1 = (float) ($this->remise_1 ?? 0);
        $remise_2 = (float) ($this->remise_2 ?? 0);
        $ristourne = (float) ($this->ristourne ?? 0);
        return round($this->montant_total - $remise_1 - $remise_2 - $ristourne - $this->montant_paye, 2);
    }

    /**
     * Statut de paiement calculé : impayée | partielle | soldée.
     */
    public function getStatutPaiementAttribute(): string
    {
        $montantPaye = $this->montant_paye;
        $reste = round(
            $this->montant_total
            - (float) ($this->remise_1 ?? 0)
            - (float) ($this->remise_2 ?? 0)
            - (float) ($this->ristourne ?? 0)
            - $montantPaye,
            2
        );

        if ($montantPaye <= 0) {
            return 'impayée';
        }
        if ($reste <= 0.009) {
            return 'soldée';
        }
        return 'partielle';
    }
}
