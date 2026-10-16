<?php

namespace App\Services\Mcf;

use App\Models\Entete;
use App\Models\Facture;
use App\Models\McfCertification;
use App\Models\McfMachineState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Simulation logicielle d'un Module de Contrôle de Facturation (MCF) conforme
 * aux spécifications SECeF BF ST 1.0 (Annexe Protocoles MCF-SFE).
 *
 * Ce service reproduit la SÉMANTIQUE des commandes du protocole (C0h, 31h,
 * 33h, 35h, 38h, ...) sans la couche transport série/USB, puisqu'aucun
 * boîtier physique n'est encore disponible. Le jour où un vrai MCF est
 * raccordé, seule une implémentation alternative de transport (pilote série)
 * a besoin d'être écrite ; les données produites ici (SIG, NIM, compteurs,
 * groupes de taxation, QR) suivent le même format que la machine réelle.
 */
class VirtualMcfService
{
    /**
     * Retourne (ou initialise) l'état de la machine virtuelle — équivalent
     * de la commande (C1h) ÉTAT DE MCF.
     */
    public function etatMachine(): McfMachineState
    {
        $etat = McfMachineState::first();

        if ($etat) {
            return $etat;
        }

        $entete = Entete::first();

        return McfMachineState::create([
            'nim' => $this->genererNim(),
            'acnt' => 1,
            'ifu' => optional($entete)->ifu,
            'compteur_total' => 0,
            'compteur_vente' => 0,
            'compteur_avoir' => 0,
            'derniere_connexion_serveur' => now(),
        ]);
    }

    /**
     * Équivalent de la commande (C2h) ÉTAT DE LA CONNEXION DU SERVEUR.
     * Comme il n'y a pas de vrai serveur DGI à contacter, on simule une
     * connexion réussie "maintenant" à chaque appel.
     */
    public function etatConnexionServeur(): array
    {
        $etat = $this->etatMachine();
        $etat->update(['derniere_connexion_serveur' => now()]);

        return [
            'transactions_envoyees'   => $etat->compteur_total,
            'transactions_disponibles' => $etat->compteur_total,
            'derniere_connexion'      => $etat->derniere_connexion_serveur,
        ];
    }

    /**
     * Certifie une facture de vente — orchestre l'équivalent du flux complet
     * C3h → C0h → 31h(xN) → 33h → 35h → 38h en une seule transaction.
     */
    public function certifierFacture(Facture $facture): McfCertification
    {
        if ($facture->certification_active) {
            throw new RuntimeException("Cette facture est déjà certifiée (SIG: {$facture->certification_active->sig}).");
        }

        $facture->loadMissing(['ElementFactures.Produit.tva', 'Contribuable']);

        if ($facture->ElementFactures->isEmpty()) {
            throw new RuntimeException("Impossible de certifier une facture sans éléments.");
        }

        return DB::transaction(function () use ($facture) {
            $etat   = $this->etatMachine();
            $entete = Entete::first();

            $detail = $this->calculerGroupesTaxation($facture);

            $etat->compteur_total++;
            $etat->compteur_vente++;

            $sig = $this->genererSig();
            $dt  = now();

            $certification = McfCertification::create([
                'facture_id'           => $facture->id,
                'type_facture'         => 'FV',
                'nature_avoir'         => null,
                'compteur_facture'     => $etat->compteur_vente,
                'compteur_total'       => $etat->compteur_total,
                'nim'                  => $etat->mid,
                'ifu'                  => optional($entete)->ifu,
                'sig'                  => $sig,
                'qr_content'           => $this->construireQrContent($etat->mid, $sig, optional($entete)->ifu ?? '', $dt),
                'date_certification'   => $dt,
                'montant_ht'           => $detail['total_ht'],
                'montant_tva'          => $detail['total_tva'],
                'montant_ts'           => $detail['total_ts'],
                'montant_ttc'          => $detail['total_ttc'],
                'detail_taxation'      => $detail['groupes'],
                'statut'               => 'certifiee',
            ]);

            $etat->update([
                'compteur_total'           => $etat->compteur_total,
                'compteur_vente'           => $etat->compteur_vente,
                'derniere_facture_date'    => $dt,
                'derniere_facture_type'    => 'FV',
                'derniere_facture_sig'     => $sig,
                'derniere_facture_numero'  => $facture->code,
                'dernier_montant'          => $detail['total_ttc'],
            ]);

            return $certification;
        });
    }

    /**
     * Annule la certification active d'une facture en émettant une facture
     * d'avoir d'annulation (FA / RAN) qui référence la certification d'origine
     * — conformément à la sémantique C0h (RT=FA, RR=RAN, RN=référence).
     */
    public function annulerFacture(Facture $facture, ?string $motif = null): McfCertification
    {
        $originale = $facture->certification_active;

        if (!$originale) {
            throw new RuntimeException("Cette facture n'a pas de certification active à annuler.");
        }

        return DB::transaction(function () use ($facture, $originale, $motif) {
            $etat   = $this->etatMachine();
            $entete = Entete::first();

            $etat->compteur_total++;
            $etat->compteur_avoir++;

            $sig = $this->genererSig();
            $dt  = now();

            $avoir = McfCertification::create([
                'facture_id'                 => $facture->id,
                'reference_certification_id' => $originale->id,
                'type_facture'               => 'FA',
                'nature_avoir'               => 'RAN',
                'compteur_facture'           => $etat->compteur_avoir,
                'compteur_total'             => $etat->compteur_total,
                'nim'                        => $etat->mid,
                'ifu'                        => optional($entete)->ifu,
                'sig'                        => $sig,
                'qr_content'                 => $this->construireQrContent($etat->mid, $sig, optional($entete)->ifu ?? '', $dt),
                'date_certification'         => $dt,
                'montant_ht'                 => $originale->montant_ht,
                'montant_tva'                => $originale->montant_tva,
                'montant_ts'                 => $originale->montant_ts,
                'montant_ttc'                => $originale->montant_ttc,
                'detail_taxation'            => $originale->detail_taxation,
                'statut'                     => 'certifiee',
            ]);

            $originale->update(['statut' => 'annulee']);

            $etat->update([
                'derniere_facture_date'   => $dt,
                'derniere_facture_type'   => 'FA',
                'derniere_facture_sig'    => $sig,
                'derniere_facture_numero' => $facture->code,
                'dernier_montant'         => $originale->montant_ttc,
            ]);

            return $avoir;
        });
    }

    /**
     * Regroupe les éléments de la facture par taux de TVA — équivalent des
     * commandes (31h) + (33h). Chaque groupe se voit attribuer une lettre
     * (A, B, C...) par ordre croissant de taux, le taux 0% devenant
     * naturellement le groupe "A" (exonéré) s'il est présent, comme dans
     * l'exemple de la spécification.
     */
    protected function calculerGroupesTaxation(Facture $facture): array
    {
        $parTaux = [];
        $totalTs = 0.0;

        foreach ($facture->ElementFactures as $element) {
            $quantite  = (float) $element->quantite;
            $prix      = (float) $element->prix_unitaire;
            $montantHt = $quantite * $prix;
            $produit   = $element->Produit;
            $taux      = (float) optional(optional($produit)->tva)->taux;

            $key = number_format($taux, 2, '.', '');
            if (!isset($parTaux[$key])) {
                $parTaux[$key] = ['taux' => $taux, 'base' => 0.0];
            }
            $parTaux[$key]['base'] += $montantHt;

            if ($produit && (float) $produit->montant_ts > 0) {
                $totalTs += (float) $produit->montant_ts * $quantite;
            }
        }

        ksort($parTaux, SORT_NUMERIC);

        $lettres   = range('A', 'P');
        $groupes   = [];
        $totalHt   = 0.0;
        $totalTva  = 0.0;
        $i = 0;

        foreach ($parTaux as $g) {
            $taxe = $g['base'] * $g['taux'] / 100;
            $groupes[] = [
                'groupe' => $lettres[$i] ?? ('G' . $i),
                'taux'   => $g['taux'],
                'base'   => round($g['base'], 2),
                'taxe'   => round($taxe, 2),
            ];
            $totalHt  += $g['base'];
            $totalTva += $taxe;
            $i++;
        }

        return [
            'groupes'   => $groupes,
            'total_ht'  => round($totalHt, 2),
            'total_tva' => round($totalTva, 2),
            'total_ts'  => round($totalTs, 2),
            'total_ttc' => round($totalHt + $totalTva + $totalTs, 2),
        ];
    }

    /**
     * Génère un code SECeF/DGI (SIG) — chaîne alphanumérique de 24
     * caractères, au même format que les exemples de la spécification
     * (ex: D357MYBKF6QSMSD2XWSEFT6S).
     */
    protected function genererSig(): string
    {
        return strtoupper(Str::random(24));
    }

    /**
     * Génère un NIM (numéro de série de machine) au format observé dans la
     * spécification : 2 lettres + 8 chiffres (ex: AB01000001).
     */
    protected function genererNim(): string
    {
        $lettres = strtoupper(Str::random(2));
        $chiffres = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

        return $lettres . $chiffres;
    }

    /**
     * Construit le contenu du QR code conformément au chapitre 5 :
     * {BFSECEF01};{MID};{SIG};{IFU};{DT}
     */
    protected function construireQrContent(string $mid, string $sig, string $ifu, $dt): string
    {
        return implode(';', [
            'BFSECEF01',
            $mid,
            $sig,
            $ifu,
            $dt->format('YmdHis'),
        ]);
    }
}
