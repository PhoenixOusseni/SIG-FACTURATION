<?php

namespace App\Services\SFE;

use App\Exceptions\SFE\BridgeException;
use App\Models\Facture;
use App\Models\FactureAvoir;
use App\Models\McfCertification;

/**
 * Authoritative local safety gate before a document reaches the physical SFE.
 * UI validation is helpful, but this also protects direct routes and API calls.
 */
class FiscalDataValidator
{
    public function validateSale(Facture $facture): void
    {
        $this->validateHeader($facture->code, $facture->Contribuable, 'facture');
        $type = SfeInvoiceType::normalize(optional($facture->Typefacture)->code);
        if (!in_array($type, SfeInvoiceType::SALE_FAMILY, true)) {
            throw new BridgeException("Type de facture « {$type} » non fiscalisable. Utilisez FV, FT, EV ou ET.");
        }
        if (!$facture->ModePaie || trim((string) $facture->ModePaie->code) === '') {
            throw new BridgeException('Sélectionnez un mode de paiement avant la fiscalisation.');
        }
        $this->validateLines($facture->ElementFactures, 'facture');
        $this->validateDiscounts($this->gross($facture->ElementFactures),
            (float) ($facture->remise_1 ?? 0), (float) ($facture->remise_2 ?? 0), 'facture');
    }

    public function validateAvoir(FactureAvoir $avoir, string $nature): void
    {
        $this->validateHeader($avoir->code, $avoir->Contribuable, 'avoir');
        $this->validateLines($avoir->ElementFactureAvoirs, 'avoir');
        $this->validateDiscounts($this->gross($avoir->ElementFactureAvoirs),
            (float) ($avoir->remise_1 ?? 0), (float) ($avoir->remise_2 ?? 0), 'avoir');

        $original = $avoir->Facture;
        if ($original && (int) $original->contribuable_id !== (int) $avoir->contribuables_id) {
            throw new BridgeException('Le client de l’avoir doit être le même que celui de la facture d’origine.');
        }
        $originalCert = $original ? $original->certification_active : null;
        if (!$originalCert) {
            return;
        }
        if (trim((string) $originalCert->nim) === '' || (int) $originalCert->compteur_total <= 0) {
            throw new BridgeException('La référence fiscale originale est incomplète (NIM ou compteur total manquant).');
        }

        $alreadyCredited = (float) McfCertification::query()
            ->where('facture_id', $original->id)
            ->whereIn('type_facture', ['FA', 'EA'])
            ->where('statut', 'certifiee')
            ->where('facture_avoir_id', '<>', $avoir->id)
            ->sum('montant_ttc');
        $remaining = max(0, round((float) $originalCert->montant_ttc - $alreadyCredited, 2));
        $amount = round((float) $avoir->montant_total, 2);
        if ($amount > $remaining + 0.01) {
            throw new BridgeException('Le montant de l’avoir (' . number_format($amount, 2, ',', ' ') .
                ' XOF) dépasse le solde fiscal disponible (' . number_format($remaining, 2, ',', ' ') . ' XOF).');
        }
        if ($nature === SfeInvoiceType::NATURE_RAN && abs($amount - $remaining) > 0.01) {
            throw new BridgeException('Pour une annulation RAN, le montant doit être exactement égal au solde fiscal (' .
                number_format($remaining, 2, ',', ' ') . ' XOF).');
        }
    }

    protected function validateHeader(?string $code, $client, string $label): void
    {
        if (trim((string) $code) === '') {
            throw new BridgeException("Le numéro interne de la {$label} est obligatoire.");
        }
        if (!$client || trim((string) $client->assujeti) === '') {
            throw new BridgeException("Sélectionnez un client avec un nom valide pour la {$label}.");
        }
        $ifu = strtoupper(trim((string) ($client->ifu ?? '')));
        if ($ifu !== '' && !preg_match('/^[0-9]{8}[A-Z]$/', $ifu)) {
            throw new BridgeException('L’IFU client doit contenir 8 chiffres suivis d’une lettre (exemple : 00001234A).');
        }
        $email = trim((string) ($client->email ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BridgeException('L’adresse e-mail du client n’est pas valide.');
        }
    }

    protected function validateLines($lines, string $label): void
    {
        if ($lines->isEmpty()) {
            throw new BridgeException("Ajoutez au moins une ligne à l’{$label} avant la fiscalisation.");
        }
        foreach ($lines->values() as $index => $line) {
            $number = $index + 1;
            $product = $line->Produit;
            if (!$product) {
                throw new BridgeException("Ligne {$number} : le produit n’existe plus.");
            }
            $designation = trim((string) ($line->designation ?: $product->designation));
            if ($designation === '') {
                throw new BridgeException("Ligne {$number} : la désignation est obligatoire.");
            }
            if ((float) $line->quantite <= 0) {
                throw new BridgeException("Ligne {$number} ({$designation}) : la quantité doit être supérieure à zéro.");
            }
            if ((float) $line->prix_unitaire <= 0) {
                throw new BridgeException("Ligne {$number} ({$designation}) : le prix doit être supérieur à zéro.");
            }
            if (!$product->tva) {
                throw new BridgeException("Ligne {$number} ({$designation}) : configurez la TVA du produit.");
            }
            $group = strtoupper(trim((string) $product->tva->code));
            if (!preg_match('/^[A-Z]$/', $group)) {
                throw new BridgeException("Ligne {$number} ({$designation}) : groupe de taxe DGI invalide.");
            }
            $rate = (float) $product->tva->taux;
            if ($rate < 0 || $rate > 100) {
                throw new BridgeException("Ligne {$number} ({$designation}) : taux TVA hors limites (0 à 100 %).");
            }
            $articleType = strtoupper(trim((string) ($product->famille_produit ?? '')));
            if (!preg_match('/^[A-Z0-9]{3,20}$/', $articleType)) {
                throw new BridgeException("Ligne {$number} ({$designation}) : type d’article DGI invalide (ex. LOCBIE).");
            }
            if ((float) ($product->montant_ts ?? 0) < 0) {
                throw new BridgeException("Ligne {$number} ({$designation}) : taxe spécifique négative interdite.");
            }
        }
    }

    protected function validateDiscounts(float $gross, float $first, float $second, string $label): void
    {
        if ($first < 0 || $second < 0) {
            throw new BridgeException("Les remises de l’{$label} ne peuvent pas être négatives.");
        }
        if ($gross <= 0 || $first + $second >= $gross) {
            throw new BridgeException("Le total des remises doit être inférieur au montant brut de l’{$label}.");
        }
    }

    protected function gross($lines): float
    {
        return (float) $lines->sum(fn ($line) => (float) $line->quantite * (float) $line->prix_unitaire);
    }
}
