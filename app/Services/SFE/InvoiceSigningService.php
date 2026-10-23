<?php

namespace App\Services\SFE;

use App\Exceptions\SFE\BridgeException;
use App\Models\Entete;
use App\Models\Facture;
use App\Models\FactureAvoir;
use App\Models\McfCertification;
use App\Services\Mcf\McfSerialDriver;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceSigningService
{
    protected $driver;
    protected $validator;

    public function __construct(McfSerialDriver $driver, FiscalDataValidator $validator)
    {
        $this->driver = $driver;
        $this->validator = $validator;
    }

    /**
     * Fiscalise une facture de vente (FV/EV) ou d'acompte (FT/ET mappés).
     */
    public function sign(Facture $facture): McfCertification
    {
        return $this->signFacture($facture);
    }

    public function signFacture(Facture $facture): McfCertification
    {
        if ($facture->is_fiscal_locked || $facture->certification_active) {
            throw new BridgeException('Cette facture est déjà fiscalisée et verrouillée.');
        }

        $facture->loadMissing([
            'ElementFactures.Produit.tva',
            'Contribuable',
            'ModePaie',
            'Typefacture',
            'Bic',
        ]);

        // Final local guard before persisted data can reach the physical SFE.
        $this->validator->validateSale($facture);

        if ($facture->ElementFactures->isEmpty()) {
            throw new BridgeException('Ajoutez au moins une ligne avant la fiscalisation.');
        }

        $appType = SfeInvoiceType::normalize(optional($facture->Typefacture)->code ?: 'FV');
        $bridgeType = SfeInvoiceType::toBridgeType($appType);

        $lines = $this->buildSaleLines($facture);
        $amount = $this->netAmountSale($facture);
        if ($amount <= 0) {
            throw new BridgeException('Le montant de la facture doit être strictement positif (FEC 1.21).');
        }

        $entete = Entete::first();
        $ifuVendeur = strtoupper(trim((string) optional($entete)->ifu));
        $sfeId = strtoupper(trim((string) optional($entete)->identifiant_sfe)) ?: $ifuVendeur;
        $opId = auth()->check() ? 'OP' . str_pad((string) auth()->id(), 2, '0', STR_PAD_LEFT) : 'OP01';
        $opNom = auth()->check() ? substr(auth()->user()->name, 0, 32) : 'Vendeur';

        $commentaires = [];
        if (!empty($facture->notes) && str_starts_with(trim($facture->notes), '{')) {
            $commentaires = json_decode($facture->notes, true) ?: [];
        }

        // Keys always present (bridge sample / validators may require them even for FV).
        $payload = [
            'numero_interne' => $facture->code,
            'type_facture' => $bridgeType,
            'mode_prix' => 'TTC',
            'categorie_psvb' => optional($facture->Bic)->code ?: '',
            'nature_avoir' => '',
            'reference_originale' => '',
            'ifu_vendeur' => $ifuVendeur,
            'sfeid' => $sfeId,
            'operateur_id' => $opId,
            'operateur_nom' => $opNom,
            'client' => $this->buildClient(optional($facture->Contribuable)),
            'lignes' => $lines,
            'paiements' => $this->buildPaiements($facture, $amount),
            'commentaires' => $commentaires,
        ];

        $response = $this->certify($payload, $facture->code);

        return DB::transaction(function () use ($facture, $response, $bridgeType) {
            $certificationDate = $this->parseCertificationDate($response['date_certification'] ?? null);
            $certification = McfCertification::firstOrCreate(
                ['sig' => $response['signature']],
                [
                    'facture_id' => $facture->id,
                    'facture_avoir_id' => null,
                    'type_facture' => $bridgeType,
                    'nature_avoir' => null,
                    'compteur_facture' => $response['compteur_type'] ?? 0,
                    'compteur_total' => $response['compteur_total'] ?? 0,
                    'nim' => $response['nim'] ?? '',
                    'ifu' => optional(Entete::first())->ifu,
                    'sig' => $response['signature'],
                    'qr_content' => $response['qrcode_data'] ?? '',
                    'date_certification' => $certificationDate,
                    'montant_ttc' => $this->netAmountSale($facture),
                    'psvb' => (float) ($response['psvb'] ?? 0),
                    'detail_taxation' => [],
                    'statut' => 'certifiee',
                ]
            );

            $facture->update([
                'statut' => 'Fiscalisée',
                'sfe_locked_at' => now(),
                'dgi_signature' => $response['signature'],
                'dgi_nim_machine' => $response['nim'] ?? null,
                'dgi_compteur' => $this->formatCompteur($response),
                'dgi_qrcode' => $response['qrcode_data'] ?? null,
            ]);

            return $certification;
        });
    }

    /**
     * Fiscalise une facture d'avoir (FA / EA) avec nature et référence originale.
     */
    public function signAvoir(FactureAvoir $avoir): McfCertification
    {
        if ($avoir->is_fiscal_locked || $avoir->certification_active) {
            throw new BridgeException('Cet avoir est déjà fiscalisé et verrouillé.');
        }

        $avoir->loadMissing([
            'ElementFactureAvoirs.Produit.tva',
            'Contribuable',
            'Facture.Typefacture',
            'Facture.ModePaie',
            'Facture.Bic',
            'Facture.McfCertifications',
            'ModePaie',
        ]);

        if ($avoir->ElementFactureAvoirs->isEmpty()) {
            throw new BridgeException('Ajoutez au moins une ligne avant la fiscalisation de l\'avoir.');
        }

        $nature = SfeInvoiceType::normalizeNature($avoir->nature_avoir)
            ?: SfeInvoiceType::natureFromMotif($avoir->motif);

        if (!$nature) {
            throw new BridgeException(
                'Précisez la nature de l\'avoir (COR, RAN, RAM ou RRR) avant fiscalisation (FEC 1.25).'
            );
        }

        // Validate the avoir and its relationship to the original sale before
        // building a fiscal reference or opening a receipt on the device.
        $this->validator->validateAvoir($avoir, $nature);

        $original = $avoir->Facture;
        $originalCert = $original ? $original->certification_active : null;

        // RRR sans original certifié : référence « RRR » (FEC 1.26).
        // Autres natures : original certifié obligatoire.
        if ($nature !== SfeInvoiceType::NATURE_RRR) {
            if (!$original) {
                throw new BridgeException('Une facture d\'origine est obligatoire pour cette nature d\'avoir.');
            }
            if (!$originalCert) {
                throw new BridgeException(
                    'La facture d\'origine doit être fiscalisée avant de certifier l\'avoir.'
                );
            }
        }

        $originalAppType = optional(optional($original)->Typefacture)->code;
        $bridgeType = SfeInvoiceType::creditTypeFromOriginal($originalAppType);
        // Prefer EA if original bridge type was EV
        if ($originalCert && SfeInvoiceType::toBridgeType($originalCert->type_facture) === 'EV') {
            $bridgeType = 'EA';
        }

        $referenceOriginale = $this->buildReferenceOriginale($nature, $originalCert);

        $lines = $this->buildAvoirLines($avoir);
        $amount = $this->netAmountAvoir($avoir);
        if ($amount <= 0) {
            throw new BridgeException('Le montant de l\'avoir doit être strictement positif (FEC 1.21).');
        }

        $paymentMode = optional($avoir->ModePaie)->code
            ?: optional(optional($original)->ModePaie)->code
            ?: 'ESP';

        $entete = Entete::first();
        $ifuVendeur = strtoupper(trim((string) optional($entete)->ifu));
        $sfeId = strtoupper(trim((string) optional($entete)->identifiant_sfe)) ?: $ifuVendeur;
        $opId = auth()->check() ? 'OP' . str_pad((string) auth()->id(), 2, '0', STR_PAD_LEFT) : 'OP01';
        $opNom = auth()->check() ? substr(auth()->user()->name, 0, 32) : 'Vendeur';

        $commentaires = [];
        if (!empty($avoir->notes) && str_starts_with(trim($avoir->notes), '{')) {
            $commentaires = json_decode($avoir->notes, true) ?: [];
        }

        $payload = [
            'numero_interne' => $avoir->code,
            'type_facture' => $bridgeType,
            'mode_prix' => 'TTC',
            'categorie_psvb' => optional(optional($original)->Bic)->code ?: '',
            'nature_avoir' => $nature,
            'reference_originale' => $referenceOriginale,
            'ifu_vendeur' => $ifuVendeur,
            'sfeid' => $sfeId,
            'operateur_id' => $opId,
            'operateur_nom' => $opNom,
            'client' => $this->buildClient(optional($avoir->Contribuable)),
            'lignes' => $lines,
            'paiements' => [[
                'mode' => $paymentMode,
                'montant' => $amount,
            ]],
            'commentaires' => $commentaires,
        ];

        $response = $this->certify($payload, $avoir->code);

        return DB::transaction(function () use ($avoir, $response, $bridgeType, $nature, $original, $originalCert, $amount) {
            $certificationDate = $this->parseCertificationDate($response['date_certification'] ?? null);
            $certification = McfCertification::firstOrCreate(
                ['sig' => $response['signature']],
                [
                    'facture_id' => $original ? $original->id : null,
                    'facture_avoir_id' => $avoir->id,
                    'reference_certification_id' => $originalCert ? $originalCert->id : null,
                    'type_facture' => $bridgeType,
                    'nature_avoir' => $nature,
                    'compteur_facture' => $response['compteur_type'] ?? 0,
                    'compteur_total' => $response['compteur_total'] ?? 0,
                    'nim' => $response['nim'] ?? '',
                    'ifu' => optional(Entete::first())->ifu,
                    'sig' => $response['signature'],
                    'qr_content' => $response['qrcode_data'] ?? '',
                    'date_certification' => $certificationDate,
                    'montant_ttc' => $amount,
                    'psvb' => (float) ($response['psvb'] ?? 0),
                    'detail_taxation' => [],
                    'statut' => 'certifiee',
                ]
            );

            $avoir->update([
                'nature_avoir' => $nature,
                'statut' => 'Fiscalisée',
                'sfe_locked_at' => now(),
                'dgi_signature' => $response['signature'],
                'dgi_nim_machine' => $response['nim'] ?? null,
                'dgi_compteur' => $this->formatCompteur($response),
                'dgi_qrcode' => $response['qrcode_data'] ?? null,
            ]);

            return $certification;
        });
    }

    protected function certify(array $payload, string $numeroInterne): array
    {
        Log::info('SFE certification request via native driver', [
            'numero_interne' => $numeroInterne,
            'type_facture' => $payload['type_facture'] ?? null,
            'nature_avoir' => $payload['nature_avoir'] ?? null,
        ]);

        $response = $this->driver->certifierPayload($payload);

        if (!($response['succes'] ?? false) || empty($response['signature'])) {
            $response['erreur'] = $this->friendlyBridgeError($response['erreur'] ?? null);
        }

        if (!($response['succes'] ?? false) || empty($response['signature'])) {
            throw new BridgeException($response['erreur'] ?? 'Le boîtier a refusé la signature fiscale.');
        }

        Log::info('SFE signature received; persisting fiscal data.', [
            'numero_interne' => $numeroInterne,
            'signature' => $response['signature'],
            'nim' => $response['nim'] ?? null,
            'compteur_type' => $response['compteur_type'] ?? null,
            'compteur_total' => $response['compteur_total'] ?? null,
            'date_certification' => $response['date_certification'] ?? null,
            'qrcode_data' => $response['qrcode_data'] ?? null,
        ]);

        return $response;
    }

    /** Translate terse protocol codes into actionable UI messages. */
    protected function friendlyBridgeError($rawError): string
    {
        $raw = trim((string) $rawError);
        if ($raw === '') {
            return 'Le boitier SFE a refuse la fiscalisation sans detail. Verifiez son etat puis reessayez.';
        }

        $upper = strtoupper($raw);
        if (preg_match('/(^|[^A-Z])RN\s*=/', $upper)) {
            return 'Reference fiscale originale refusee. Verifiez que la facture origine est fiscalisee et utilisez le format NIM-compteur total. Detail SFE : ' . $raw;
        }
        if (preg_match('/(^|[^A-Z])TR($|[^A-Z])/', $upper)) {
            return 'Article refuse par le SFE. Verifiez designation, type article DGI, groupe de taxe, TVA, quantite et prix. Detail SFE : ' . $raw;
        }
        if (str_contains($upper, 'NO RECEIPT')) {
            return 'Aucune facture fiscale ouverte sur le boitier. Actualisez son etat puis relancez. Detail SFE : ' . $raw;
        }
        if (str_contains($upper, 'TIMEOUT') || str_contains($upper, 'TIMED OUT')) {
            return 'Le boitier SFE ne repond pas. Verifiez le Bridge et le port COM puis reessayez. Detail : ' . $raw;
        }
        if (str_contains($upper, 'CONNEX') || str_contains($upper, 'PORT COM')) {
            return 'Connexion au boitier SFE impossible. Verifiez le Bridge et le port COM. Detail : ' . $raw;
        }

        return 'Fiscalisation refusee par le SFE : ' . $raw;
    }

    protected function buildClient($client): array
    {
        $type = strtoupper(trim((string) ($client->type_client ?? 'CC')));
        if (!in_array($type, ['CC', 'PM', 'PP', 'PC'], true)) {
            $type = 'CC';
        }

        return [
            'type_client' => $type,
            'nom' => $client->assujeti ?? '',
            'telephone' => $client->telephone ?? '',
            'adresse' => $client->adresse ?? '',
            'ifu' => $client->ifu ?? '',
            'rccm' => $client->rccm ?? '',
            'email' => $client->email ?? null,
        ];
    }

    /**
     * Bridge accepts one or more payment modes; sum must equal invoice total.
     * Prefer existing règlements when they cover the net; otherwise single mode.
     */
    protected function buildPaiements(Facture $facture, float $netAmount): array
    {
        $defaultMode = optional($facture->ModePaie)->code ?: 'ESP';

        if (method_exists($facture, 'Reglements')) {
            $reglements = $facture->Reglements()->get();
            if ($reglements->isNotEmpty()) {
                $payments = [];
                $sum = 0.0;
                foreach ($reglements as $reg) {
                    $montant = round((float) $reg->montant, 2);
                    if ($montant <= 0) {
                        continue;
                    }
                    $mode = $defaultMode;
                    if (!empty($reg->modepaie_id) && class_exists(\App\Models\ModePaie::class)) {
                        $mode = optional(\App\Models\ModePaie::find($reg->modepaie_id))->code ?: $defaultMode;
                    }
                    $payments[] = ['mode' => $mode, 'montant' => $montant];
                    $sum += $montant;
                }
                if ($payments && abs($sum - $netAmount) <= 0.01) {
                    return $payments;
                }
                // Partial règlements: still one fiscal payment line for the full certified total.
            }
        }

        return [['mode' => $defaultMode, 'montant' => $netAmount]];
    }

    protected function buildSaleLines(Facture $facture): array
    {
        $grossHt = $facture->ElementFactures->sum(function ($line) {
            return (float) $line->quantite * (float) $line->prix_unitaire;
        });
        $discount = (float) $facture->remise_1 + (float) $facture->remise_2;
        $discountFactor = $grossHt > 0 ? max(0, $grossHt - $discount) / $grossHt : 1;

        return $facture->ElementFactures->map(function ($line) use ($discountFactor) {
            $product = $line->Produit;
            $tax = optional($product)->tva;
            $rate = (float) optional($tax)->taux;
            $unitHtAfterDiscount = (float) $line->prix_unitaire * $discountFactor;
            $unitTtc = round($unitHtAfterDiscount * (1 + $rate / 100), 2);
            $qty = (float) $line->quantite;
            if ($qty <= 0 || $unitTtc < 0) {
                throw new BridgeException('Ligne invalide : quantité et montant doivent être positifs (FEC 1.22).');
            }

            $tsUnit = 0.0;
            if ($product) {
                if ($product->type_ts === 'POURCENTAGE' && (float) ($product->pourcentage_ts ?? 0) > 0) {
                    $tsUnit = round(((float) $line->prix_unitaire) * ((float) $product->pourcentage_ts / 100.0), 2);
                } else {
                    $tsUnit = (float) ($product->montant_ts ?? 0);
                }
            }

            return [
                'designation' => $line->designation ?: optional($product)->designation,
                'quantite' => $qty,
                'prix_unitaire' => $unitTtc,
                'taux_tva' => $rate,
                'groupe_taxe' => strtoupper(optional($tax)->code ?: 'A'),
                'type_article_dgi' => $product->famille_produit ?? 'LOCBIE',
                'montant_ts' => $tsUnit,
            ];
        })->values()->all();
    }

    protected function buildAvoirLines(FactureAvoir $avoir): array
    {
        $grossHt = $avoir->ElementFactureAvoirs->sum(function ($line) {
            return (float) $line->quantite * (float) $line->prix_unitaire;
        });
        $discount = (float) ($avoir->remise_1 ?? 0) + (float) ($avoir->remise_2 ?? 0);
        $discountFactor = $grossHt > 0 ? max(0, $grossHt - $discount) / $grossHt : 1;

        return $avoir->ElementFactureAvoirs->map(function ($line) use ($discountFactor) {
            $product = $line->Produit;
            $tax = optional($product)->tva;
            $rate = (float) optional($tax)->taux;
            $unitHtAfterDiscount = (float) $line->prix_unitaire * $discountFactor;
            $unitTtc = round($unitHtAfterDiscount * (1 + $rate / 100), 2);
            $qty = (float) $line->quantite;
            if ($qty <= 0 || $unitTtc < 0) {
                throw new BridgeException('Ligne d\'avoir invalide : quantité et montant doivent être positifs (FEC 1.22).');
            }

            $tsUnit = 0.0;
            if ($product) {
                if ($product->type_ts === 'POURCENTAGE' && (float) ($product->pourcentage_ts ?? 0) > 0) {
                    $tsUnit = round(((float) $line->prix_unitaire) * ((float) $product->pourcentage_ts / 100.0), 2);
                } else {
                    $tsUnit = (float) ($product->montant_ts ?? 0);
                }
            }

            return [
                'designation' => optional($product)->designation ?: 'Article',
                'quantite' => $qty,
                'prix_unitaire' => $unitTtc,
                'taux_tva' => $rate,
                'groupe_taxe' => strtoupper(optional($tax)->code ?: 'A'),
                'type_article_dgi' => optional($product)->famille_produit ?? 'LOCBIE',
                'montant_ts' => $tsUnit,
            ];
        })->values()->all();
    }

    protected function netAmountSale(Facture $facture): float
    {
        return max(0, round(
            $facture->montant_total - (float) $facture->remise_1 - (float) $facture->remise_2,
            2
        ));
    }

    protected function netAmountAvoir(FactureAvoir $avoir): float
    {
        return max(0, round($avoir->montant_total, 2));
    }

    /**
     * Construit reference_originale pour le bridge.
     * Format documenté : NIM-compteur (ex. EL02000018-1-1254).
     */
    protected function buildReferenceOriginale(string $nature, ?McfCertification $originalCert): string
    {
        if ($nature === SfeInvoiceType::NATURE_RRR && !$originalCert) {
            return 'RRR';
        }

        if (!$originalCert) {
            throw new BridgeException('Référence fiscale originale introuvable pour l\'avoir.');
        }

        $nim = trim((string) $originalCert->nim);
        $cType = trim((string) $originalCert->compteur_facture);
        $cTotal = trim((string) $originalCert->compteur_total);

        if ($nim === '') {
            throw new BridgeException('NIM de la certification originale manquant.');
        }

        // Prefer compact "nim-type-total" when both counters exist.
        if ($cTotal !== '') {
            return $nim . '-' . $cTotal;
        }

        if ($cType !== '') {
            return $nim . '-' . $cType;
        }

        return $nim;
    }

    protected function formatCompteur(array $response): string
    {
        return trim(($response['compteur_type'] ?? '') . '/' . ($response['compteur_total'] ?? ''), '/');
    }

    protected function parseCertificationDate($value): Carbon
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }
        $value = trim((string) $value);
        if ($value === '') {
            return now();
        }
        if (preg_match('/^\d{14}$/', $value)) {
            return Carbon::createFromFormat('YmdHis', $value);
        }

        return Carbon::parse($value);
    }
}
