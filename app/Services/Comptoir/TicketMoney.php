<?php

namespace App\Services\Comptoir;

use App\Models\ElementTicketComptoir;

/**
 * Shared HT / TVA / TTC formulas for tickets (design D13).
 * Browser calc in comptoir-pos.js must mirror these rules.
 */
class TicketMoney
{
    public static function tolerance(): float
    {
        return (float) config('comptoir.money_tolerance', 0.009);
    }

    /**
     * @param  array  $lignes  each: quantite, prix_unitaire, tva_taux
     * @return array{montant_ht: float, montant_tva: float, montant_ttc_brut: float, montant_ttc: float, lignes: array}
     */
    public static function computeTicket(array $lignes, float $remise = 0.0): array
    {
        $ht = 0.0;
        $tva = 0.0;
        $computedLines = [];

        foreach ($lignes as $line) {
            $q = (float) ($line['quantite'] ?? 0);
            $pu = (float) ($line['prix_unitaire'] ?? 0);
            $taux = (float) ($line['tva_taux'] ?? 0);
            $parts = ElementTicketComptoir::computeLine($q, $pu, $taux);
            $ht += $parts['montant_ht'];
            $tva += $parts['montant_tva'];
            $computedLines[] = array_merge($line, $parts);
        }

        $ht = round($ht, 2);
        $tva = round($tva, 2);
        $ttcBrut = round($ht + $tva, 2);
        $remise = max(0.0, round($remise, 2));
        $ttc = round(max(0.0, $ttcBrut - $remise), 2);

        return [
            'montant_ht' => $ht,
            'montant_tva' => $tva,
            'montant_ttc_brut' => $ttcBrut,
            'montant_ttc' => $ttc,
            'remise' => $remise,
            'lignes' => $computedLines,
        ];
    }

    public static function nearlyEqual(float $a, float $b): bool
    {
        return abs($a - $b) <= self::tolerance();
    }
}
