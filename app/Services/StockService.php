<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\MouvementStock;
use App\Models\Produit;

class StockService
{
    /**
     * Entrée de stock (achat fournisseur, réception, ajustement positif).
     */
    public static function entree(
        int $produitId,
        ?int $serviceId,
        float $quantite,
        string $refType = '',
        ?int $refId = null,
        string $note = ''
    ): void {
        $produit = Produit::find($produitId);
        if (!$produit || $produit->isService() || !$serviceId || $quantite <= 0) return;

        static::ajusterStock($produitId, $serviceId, $quantite);
        $produit->increment('qte', $quantite);
        static::logMouvement($produitId, $serviceId, 'entree', $quantite, $refType, $refId, $note);
    }

    /**
     * Sortie de stock (vente client, consommation, ajustement négatif).
     */
    public static function sortie(
        int $produitId,
        ?int $serviceId,
        float $quantite,
        string $refType = '',
        ?int $refId = null,
        string $note = ''
    ): void {
        $produit = Produit::find($produitId);
        if (!$produit || $produit->isService() || !$serviceId || $quantite <= 0) return;

        static::ajusterStock($produitId, $serviceId, -$quantite);
        $produit->decrement('qte', $quantite);
        static::logMouvement($produitId, $serviceId, 'sortie', $quantite, $refType, $refId, $note);
    }

    /**
     * Transfert inter-dépôts : sortie du dépôt source, entrée dans le dépôt destination.
     */
    public static function transferer(
        int $produitId,
        int $srcId,
        int $dstId,
        float $quantite,
        int $transfertId
    ): void {
        static::ajusterStock($produitId, $srcId, -$quantite);
        static::ajusterStock($produitId, $dstId, $quantite);
        static::logMouvement($produitId, $srcId, 'transfert_sortie', $quantite, 'transfert', $transfertId, "Transfert #{$transfertId}");
        static::logMouvement($produitId, $dstId, 'transfert_entree', $quantite, 'transfert', $transfertId, "Transfert #{$transfertId}");
    }

    /**
     * Ajustement manuel (inventaire physique).
     */
    public static function ajustement(
        int $produitId,
        int $serviceId,
        float $nouvelleQte,
        string $note = ''
    ): void {
        $stock = Stock::firstOrCreate(
            ['produit_id' => $produitId, 'service_id' => $serviceId],
            ['quantite' => 0]
        );

        $delta = $nouvelleQte - (float) $stock->quantite;
        $stock->quantite = $nouvelleQte;
        $stock->save();

        // Répercuter le delta sur le stock global du produit
        if ($delta != 0) {
            Produit::where('id', $produitId)->increment('qte', $delta);
        }

        static::logMouvement($produitId, $serviceId, 'ajustement', abs($delta), 'ajustement', null, $note ?: "Ajustement inventaire (delta: " . ($delta >= 0 ? '+' : '') . $delta . ")");
    }

    private static function ajusterStock(int $produitId, int $serviceId, float $delta): void
    {
        $stock = Stock::firstOrCreate(
            ['produit_id' => $produitId, 'service_id' => $serviceId],
            ['quantite' => 0]
        );
        $stock->quantite = (float) $stock->quantite + $delta;
        $stock->save();
    }

    private static function logMouvement(
        int $produitId,
        int $serviceId,
        string $type,
        float $quantite,
        string $refType,
        ?int $refId,
        string $note
    ): void {
        MouvementStock::create([
            'produit_id'     => $produitId,
            'service_id'     => $serviceId,
            'type'           => $type,
            'quantite'       => $quantite,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'note'           => $note,
            'user_id'        => auth()->id(),
        ]);
    }
}
