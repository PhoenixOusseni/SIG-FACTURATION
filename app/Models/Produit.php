<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;

    protected $guarded = [

    ];

    public function tva()
    {
        return $this->belongsTo(Tva::class);
    }

    public function bic()
    {
        return $this->belongsTo(Bic::class);
    }

    /**
     * Détermine si le produit est une prestation de service (sans inventaire physique).
     */
    public function isService(): bool
    {
        return in_array($this->famille_produit, ['LOCSER', 'IMPSER']);
    }

    /**
     * Détermine si le produit est un bien physique (suivi de stock).
     */
    public function isBien(): bool
    {
        return !$this->isService();
    }

    /**
     * Code fiscal DGI de l'article (LOCBIE / IMPBIE / LOCSER / IMPSER).
     */
    public function getTypeDgiAttribute(): string
    {
        $valid = ['LOCBIE', 'IMPBIE', 'LOCSER', 'IMPSER'];
        if (!empty($this->famille_produit) && in_array(strtoupper($this->famille_produit), $valid, true)) {
            return strtoupper($this->famille_produit);
        }
        return $this->isService() ? 'LOCSER' : 'LOCBIE';
    }

    /**
     * Libellé humain du type d'article.
     */
    public function getTypeLibelleAttribute(): string
    {
        $map = [
            'LOCBIE' => 'Bien local',
            'IMPBIE' => 'Bien importé',
            'LOCSER' => 'Prestation locale',
            'IMPSER' => 'Prestation importée',
        ];
        return $map[$this->type_dgi] ?? ($this->isService() ? 'Prestation de service' : 'Bien');
    }

    /**
     * Libellé humain du groupe de taxation TVA.
     */
    public function getGroupeTvaLibelleAttribute(): string
    {
        if ($this->tva) {
            $taux = (float) $this->tva->taux;
            return "Groupe {$this->tva->code} ({$taux}%)";
        }
        return 'Exonéré (0%)';
    }

    /**
     * Libellé fiscal complet et standardisé selon les normes DGI.
     * Format : [Code] Désignation (Stock: X) — [Groupe TVA (Taux%)] — [TYPE_DGI] (Stock affiché uniquement pour les biens physiques)
     */
    public function getLabelFiscalAttribute(): string
    {
        $code = $this->code ? "[{$this->code}] " : '';
        $nom = $this->designation ?: 'Article sans nom';
        $stock = $this->isBien() ? ' (Stock: ' . number_format((float) ($this->qte ?? 0), 0, ',', ' ') . ')' : '';
        $groupe = $this->getGroupeTvaLibelleAttribute();
        $type = $this->type_dgi;

        return "{$code}{$nom}{$stock} — [{$groupe}] — [{$type}]";
    }

    /**
     * Prix unitaire Toutes Taxes Comprises (TTC) incluant TS et TVA.
     */
    public function getPrixUnitaireTtcAttribute(): float
    {
        $puHt = (float) ($this->prix_unitaire ?? 0);
        $ts = 0.0;
        if ($this->type_ts === 'MONTANT') {
            $ts = (float) ($this->montant_ts ?? 0);
        } elseif ($this->type_ts === 'POURCENTAGE') {
            $ts = $puHt * ((float) ($this->pourcentage_ts ?? 0) / 100);
        }
        $tauxTva = (float) optional($this->tva)->taux;
        return round(($puHt + $ts) * (1 + $tauxTva / 100), 2);
    }
}
