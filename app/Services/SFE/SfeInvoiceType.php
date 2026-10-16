<?php

namespace App\Services\SFE;

/**
 * Mapping between app typefacture codes (FEC 05 §1.7) and bridge type_facture.
 *
 * Bridge contract documents FV|FA|EV|EA. Acompte subtypes FT/ET stay in the app
 * for print/reporting and map to FV/EV for the MCF.
 */
class SfeInvoiceType
{
    public const SALE_FAMILY = ['FV', 'FT', 'EV', 'ET'];
    public const CREDIT_FAMILY = ['FA', 'EA'];

    public const NATURE_COR = 'COR';
    public const NATURE_RAN = 'RAN';
    public const NATURE_RAM = 'RAM';
    public const NATURE_RRR = 'RRR';

    public const NATURES = [
        self::NATURE_COR => 'Correction',
        self::NATURE_RAN => 'Annulation',
        self::NATURE_RAM => 'Avoir suite reprise de biens/services',
        self::NATURE_RRR => 'Remise, ristourne, rabais',
    ];

    /** Motif métier → nature DGI par défaut (FEC 1.25). */
    public const MOTIF_TO_NATURE = [
        'erreur' => self::NATURE_COR,
        'annulation' => self::NATURE_RAN,
        'retour' => self::NATURE_RAM,
        'produit_defectueux' => self::NATURE_RAM,
        'remise' => self::NATURE_RRR,
    ];

    public static function normalize(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        return $code !== '' ? $code : 'FV';
    }

    /**
     * Code envoyé au bridge (FV|FA|EV|EA).
     */
    public static function toBridgeType(?string $appCode): string
    {
        $code = self::normalize($appCode);
        $map = [
            'FV' => 'FV',
            'FT' => 'FV', // acompte → vente MCF
            'EV' => 'EV',
            'ET' => 'EV', // acompte export → export MCF
            'FA' => 'FA',
            'EA' => 'EA',
        ];

        return $map[$code] ?? 'FV';
    }

    public static function isSaleFamily(?string $code): bool
    {
        return in_array(self::normalize($code), self::SALE_FAMILY, true);
    }

    public static function isCreditFamily(?string $code): bool
    {
        return in_array(self::normalize($code), self::CREDIT_FAMILY, true);
    }

    public static function isExport(?string $code): bool
    {
        return in_array(self::normalize($code), ['EV', 'ET', 'EA'], true);
    }

    public static function isAcompte(?string $code): bool
    {
        return in_array(self::normalize($code), ['FT', 'ET'], true);
    }

    /**
     * Type d'avoir fiscal selon le type de la facture d'origine.
     */
    public static function creditTypeFromOriginal(?string $originalAppCode): string
    {
        return self::isExport($originalAppCode) ? 'EA' : 'FA';
    }

    public static function natureFromMotif(?string $motif): ?string
    {
        $motif = strtolower(trim((string) $motif));
        return self::MOTIF_TO_NATURE[$motif] ?? null;
    }

    public static function isValidNature(?string $nature): bool
    {
        return array_key_exists(strtoupper(trim((string) $nature)), self::NATURES);
    }

    public static function normalizeNature(?string $nature): ?string
    {
        $nature = strtoupper(trim((string) $nature));
        return self::isValidNature($nature) ? $nature : null;
    }
}
