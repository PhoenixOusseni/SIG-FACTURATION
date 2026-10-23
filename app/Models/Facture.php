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

    /**
     * Détermine si la facture a déjà subi au moins un avoir fiscal.
     */
    public function aSubiAvoir(): bool
    {
        if ($this->relationLoaded('FactureAvoirs')) {
            return $this->getRelation('FactureAvoirs')->isNotEmpty();
        }
        return $this->FactureAvoirs()->exists();
    }

    /**
     * Détermine si la facture a subi une annulation totale (RAN ou cumul >= montant).
     */
    public function aAvoirTotal(): bool
    {
        if (strtoupper((string) $this->statut) === 'ANNULÉE PAR AVOIR' || strtoupper((string) $this->statut) === 'ANNULÉE') {
            return true;
        }

        $avoirs = $this->relationLoaded('FactureAvoirs')
            ? $this->getRelation('FactureAvoirs')
            : $this->FactureAvoirs()->with('ElementFactureAvoirs.Produit.tva')->get();

        foreach ($avoirs as $av) {
            if (strtoupper((string) $av->nature_avoir) === 'RAN') {
                return true;
            }
        }

        if ($avoirs->isNotEmpty() && $this->montant_total > 0) {
            $totalAvoir = 0.0;
            foreach ($avoirs as $av) {
                $totalAvoir += (float) $av->montant_total;
            }
            if ($totalAvoir >= ($this->montant_total - 1.0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Solde restant éligible à un avoir fiscal.
     */
    public function soldeAvoirDisponible(): float
    {
        if ($this->aAvoirTotal()) {
            return 0.0;
        }

        $avoirs = $this->relationLoaded('FactureAvoirs')
            ? $this->getRelation('FactureAvoirs')
            : $this->FactureAvoirs()->with('ElementFactureAvoirs.Produit.tva')->get();

        $totalAvoir = 0.0;
        foreach ($avoirs as $av) {
            if ($av->statut === 'brouillon') {
                continue;
            }
            $totalAvoir += (float) $av->montant_total;
        }

        return max(0.0, round($this->montant_total - $totalAvoir, 2));
    }

    /**
     * Détermine si cette facture peut encore subir un avoir fiscal.
     */
    public function peutSubirAvoir(): bool
    {
        if ($this->aAvoirTotal()) {
            return false;
        }
        return $this->soldeAvoirDisponible() > 0.009;
    }

    /**
     * Détermine si la facture a déjà fait l'objet d'un avoir partiel.
     */
    public function aDejaAvoirPartiel(): bool
    {
        return $this->aSubiAvoir() && !$this->aAvoirTotal();
    }

    /**
     * L'annulation totale RAN est interdite dès que la facture a déjà subi au moins un avoir antérieur.
     */
    public function canRan(): bool
    {
        return !$this->aSubiAvoir();
    }

    /**
     * Calcule le total des quantités d'un produit déjà créditées/retournées par des avoirs sur cette facture.
     */
    public function quantiteAvoirProduit(int $produitId): float
    {
        $avoirs = $this->relationLoaded('FactureAvoirs')
            ? $this->getRelation('FactureAvoirs')
            : $this->FactureAvoirs()->with('ElementFactureAvoirs')->get();

        $qte = 0.0;
        foreach ($avoirs as $av) {
            if ($av->statut === 'brouillon') {
                continue;
            }
            foreach ($av->ElementFactureAvoirs as $elAv) {
                if ((int) $elAv->produits_id === $produitId) {
                    $qte += (float) $elAv->quantite;
                }
            }
        }
        return round($qte, 2);
    }

    /**
     * Calcule la quantité résiduelle nette disponible pour un produit donné sur cette facture.
     */
    public function quantiteRestanteProduit(int $produitId, float $quantiteInitiale): float
    {
        $dejaAvoir = $this->quantiteAvoirProduit($produitId);
        return max(0.0, round($quantiteInitiale - $dejaAvoir, 2));
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
            $quantite  = (float) $el->quantite;
            $montantHt = $quantite * (float) $el->prix_unitaire;
            $prod      = $el->Produit;
            $tsUnit    = 0.0;
            if ($prod) {
                if ($prod->type_ts === 'POURCENTAGE' && (float) ($prod->pourcentage_ts ?? 0) > 0) {
                    $tsUnit = round(((float) $el->prix_unitaire) * ((float) $prod->pourcentage_ts / 100.0), 2);
                } else {
                    $tsUnit = (float) ($prod->montant_ts ?? 0);
                }
            }
            $tsTotal   = $tsUnit * $quantite;

            $tauxTva   = (float) optional(optional($el->Produit)->tva)->taux;
            $baseTva   = $montantHt + $tsTotal;
            $tva       = $baseTva * $tauxTva / 100;
            $ttc       = $baseTva + $tva;

            $tauxBic   = (float) optional($this->Bic)->taux;
            // PSVB applicable exclusivement aux biens matériels (LOCBIE, IMPBIE)
            $bic       = ($prod && $prod->isBien()) ? ($ttc * $tauxBic / 100) : 0.0;

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
     * Solde restant dû par le client (Total TTC + BIC − Remises − Avoirs Fiscaux − Règlements).
     */
    public function getResteAPayerAttribute(): float
    {
        if ($this->aAvoirTotal()) {
            return 0.0;
        }

        $remise_1 = (float) ($this->remise_1 ?? 0);
        $remise_2 = (float) ($this->remise_2 ?? 0);
        $ristourne = (float) ($this->ristourne ?? 0);

        $totalAvoir = 0.0;
        $avoirs = $this->relationLoaded('FactureAvoirs')
            ? $this->getRelation('FactureAvoirs')
            : $this->FactureAvoirs()->get();

        foreach ($avoirs as $av) {
            $totalAvoir += (float) $av->montant_total;
        }

        $netApresAvoir = max(0.0, round($this->montant_total - $remise_1 - $remise_2 - $ristourne - $totalAvoir, 2));
        return max(0.0, round($netApresAvoir - $this->montant_paye, 2));
    }

    /**
     * Trop-perçu éventuel en faveur du client suite à un avoir sur facture déjà réglée.
     */
    public function getTropPercuAttribute(): float
    {
        $totalAvoir = 0.0;
        $avoirs = $this->relationLoaded('FactureAvoirs')
            ? $this->getRelation('FactureAvoirs')
            : $this->FactureAvoirs()->get();

        foreach ($avoirs as $av) {
            $totalAvoir += (float) $av->montant_total;
        }

        $netApresAvoir = max(0.0, round($this->montant_total - (float) ($this->remise_1 ?? 0) - (float) ($this->remise_2 ?? 0) - (float) ($this->ristourne ?? 0) - $totalAvoir, 2));
        return max(0.0, round($this->montant_paye - $netApresAvoir, 2));
    }

    /**
     * Statut de paiement calculé : impayée | partielle | soldée | annulée.
     */
    public function getStatutPaiementAttribute(): string
    {
        if ($this->aAvoirTotal()) {
            return 'annulée';
        }

        $montantPaye = $this->montant_paye;
        $reste = $this->reste_a_payer;

        if ($montantPaye <= 0) {
            return 'impayée';
        }
        if ($reste <= 0.009) {
            return 'soldée';
        }
        return 'partielle';
    }

    /**
     * Détermine si la facture est certifiée par le SFE / MCF (verrouillée).
     */
    public function isCertified(): bool
    {
        return strtolower((string) $this->statut) === 'certifiee' 
            || !empty($this->sfe_signature) 
            || !is_null($this->certification_active);
    }

    /**
     * Retourne les 8 lignes de commentaires DGI et mentions légales sous forme de tableau associatif.
     */
    public function getCommentairesDgiAttribute(): array
    {
        if (empty($this->notes)) {
            return [];
        }
        $decoded = json_decode($this->notes, true);
        return is_array($decoded) ? $decoded : ['mention' => $this->notes];
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
