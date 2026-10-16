<?php

namespace App\Services\Comptoir;

use App\Models\ModePaie;
use App\Models\Produit;
use App\Models\SessionCaisse;
use App\Models\TicketComptoir;
use App\Models\ElementTicketComptoir;
use App\Models\ReglementTicket;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TicketComptoirService
{
    /**
     * Allocate next numero and build FACT-{token}-{seq}/{dd}/{mm}/{yyyy} (D2).
     */
    public function allocateCode(SessionCaisse $session): array
    {
        $session = SessionCaisse::lockForUpdate()->findOrFail($session->id);
        $session->loadMissing('Caisse');

        $next = (int) $session->dernier_numero + 1;
        $session->dernier_numero = $next;
        $session->save();

        $token = $session->Caisse->codeToken();
        $date = Carbon::parse($session->date_metier);
        $seq = str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        if ($next >= 1000) {
            $seq = (string) $next;
        }

        $code = sprintf(
            'FACT-%s-%s/%s/%s/%s',
            $token,
            $seq,
            $date->format('d'),
            $date->format('m'),
            $date->format('Y')
        );

        return ['numero' => $next, 'code' => $code];
    }

    public function createDraft(SessionCaisse $session, User $user, array $payload = []): TicketComptoir
    {
        if (!$session->allowsSales()) {
            throw new RuntimeException('La caisse n\'est pas ouverte aux ventes (session en arrêt ou fermée).');
        }

        return DB::transaction(function () use ($session, $user, $payload) {
            $alloc = $this->allocateCode($session);
            $ticket = TicketComptoir::create([
                'code' => $alloc['code'],
                'numero' => $alloc['numero'],
                'session_caisse_id' => $session->id,
                'caisse_id' => $session->caisse_id,
                'date_metier' => $session->date_metier,
                'contribuable_id' => $payload['contribuable_id'] ?? null,
                'client_libre' => $payload['client_libre'] ?? null,
                'user_id' => $payload['user_id'] ?? $user->id,
                'statut' => TicketComptoir::STATUT_BROUILLON,
                'remise' => (float) ($payload['remise'] ?? 0),
            ]);

            if (!empty($payload['lignes']) && is_array($payload['lignes'])) {
                $this->syncLignes($ticket, $payload['lignes'], (float) ($payload['remise'] ?? 0));
            }

            return $ticket->fresh(['Elements', 'Contribuable', 'User', 'Reglements']);
        });
    }

    public function updateCart(TicketComptoir $ticket, array $payload): TicketComptoir
    {
        if (!$ticket->isEditable()) {
            throw new RuntimeException('Ce ticket n\'est plus modifiable.');
        }

        $session = $ticket->SessionCaisse;
        if (!$session || !$session->allowsSales()) {
            // allow updating held tickets while session open only
            if (!$session || $session->isFermee()) {
                throw new RuntimeException('Session de caisse invalide pour modifier ce ticket.');
            }
        }

        return DB::transaction(function () use ($ticket, $payload) {
            $ticket = TicketComptoir::lockForUpdate()->findOrFail($ticket->id);
            if (!$ticket->isEditable()) {
                throw new RuntimeException('Ce ticket n\'est plus modifiable.');
            }

            $ticket->update([
                'contribuable_id' => array_key_exists('contribuable_id', $payload)
                    ? $payload['contribuable_id']
                    : $ticket->contribuable_id,
                'client_libre' => array_key_exists('client_libre', $payload)
                    ? $payload['client_libre']
                    : $ticket->client_libre,
                'user_id' => $payload['user_id'] ?? $ticket->user_id,
                'remise' => array_key_exists('remise', $payload)
                    ? (float) $payload['remise']
                    : $ticket->remise,
            ]);

            if (array_key_exists('lignes', $payload) && is_array($payload['lignes'])) {
                $this->syncLignes($ticket, $payload['lignes'], (float) $ticket->remise);
            } else {
                $this->recomputeTotals($ticket);
            }

            return $ticket->fresh(['Elements', 'Contribuable', 'User', 'Reglements']);
        });
    }

    public function syncLignes(TicketComptoir $ticket, array $lignes, float $remise = 0.0): void
    {
        $ticket->Elements()->delete();

        $prepared = [];
        foreach ($lignes as $line) {
            $produitId = (int) ($line['produits_id'] ?? 0);
            if ($produitId <= 0) {
                continue;
            }
            $produit = Produit::with('tva')->find($produitId);
            if (!$produit) {
                throw new RuntimeException('Produit introuvable (#' . $produitId . ').');
            }

            $qte = (float) ($line['quantite'] ?? 0);
            if ($qte <= 0) {
                continue;
            }

            $pu = array_key_exists('prix_unitaire', $line)
                ? (float) $line['prix_unitaire']
                : (float) ($produit->prix_unitaire ?? $produit->prix ?? 0);

            $tvaTaux = array_key_exists('tva_taux', $line)
                ? (float) $line['tva_taux']
                : (float) optional($produit->tva)->taux;

            $designation = $line['designation'] ?? $produit->designation;

            $prepared[] = [
                'produits_id' => $produitId,
                'designation' => $designation,
                'quantite' => $qte,
                'prix_unitaire' => $pu,
                'tva_taux' => $tvaTaux,
            ];
        }

        $money = TicketMoney::computeTicket($prepared, $remise);

        foreach ($money['lignes'] as $row) {
            ElementTicketComptoir::create([
                'ticket_comptoir_id' => $ticket->id,
                'produits_id' => $row['produits_id'],
                'designation' => $row['designation'] ?? null,
                'quantite' => $row['quantite'],
                'prix_unitaire' => $row['prix_unitaire'],
                'tva_taux' => $row['tva_taux'],
                'montant_ht' => $row['montant_ht'],
                'montant_tva' => $row['montant_tva'],
                'montant_ttc_ligne' => $row['montant_ttc_ligne'],
            ]);
        }

        $ticket->update([
            'remise' => $money['remise'],
            'montant_ht' => $money['montant_ht'],
            'montant_ttc' => $money['montant_ttc'],
        ]);
    }

    public function recomputeTotals(TicketComptoir $ticket): void
    {
        $lignes = $ticket->Elements()->get()->map(function ($el) {
            return [
                'quantite' => $el->quantite,
                'prix_unitaire' => $el->prix_unitaire,
                'tva_taux' => $el->tva_taux,
            ];
        })->all();

        $money = TicketMoney::computeTicket($lignes, (float) $ticket->remise);
        $ticket->update([
            'montant_ht' => $money['montant_ht'],
            'montant_ttc' => $money['montant_ttc'],
        ]);
    }

    public function mettreEnAttente(TicketComptoir $ticket): TicketComptoir
    {
        if ($ticket->statut !== TicketComptoir::STATUT_BROUILLON) {
            throw new RuntimeException('Seul un ticket brouillon peut être mis en attente.');
        }
        if ($ticket->Elements()->count() === 0) {
            throw new RuntimeException('Impossible de mettre en attente un ticket vide.');
        }

        $ticket->update(['statut' => TicketComptoir::STATUT_EN_ATTENTE]);

        return $ticket->fresh(['Elements', 'Contribuable', 'User']);
    }

    public function reprendre(TicketComptoir $ticket, SessionCaisse $session): TicketComptoir
    {
        if ($ticket->statut !== TicketComptoir::STATUT_EN_ATTENTE) {
            throw new RuntimeException('Seul un ticket en attente peut être repris.');
        }
        if ((int) $ticket->session_caisse_id !== (int) $session->id) {
            throw new RuntimeException('Ce ticket n\'appartient pas à la session courante.');
        }
        if (!$session->allowsSales()) {
            throw new RuntimeException('Reprenez la session (sortie d\'arrêt) avant de vendre.');
        }

        $ticket->update(['statut' => TicketComptoir::STATUT_BROUILLON]);

        return $ticket->fresh(['Elements', 'Contribuable', 'User', 'Reglements']);
    }

    public function annuler(TicketComptoir $ticket, User $user, ?string $motif = null): TicketComptoir
    {
        if (!$ticket->isEditable()) {
            throw new RuntimeException('Ticket déjà encaissé : annulation interdite (v1).');
        }

        $ticket->update([
            'statut' => TicketComptoir::STATUT_ANNULE,
            'annule_at' => now(),
            'annule_par' => $user->id,
            'motif_annulation' => $motif,
        ]);

        return $ticket->fresh();
    }

    public function supprimer(TicketComptoir $ticket): void
    {
        if (!$ticket->isEditable()) {
            throw new RuntimeException('Seul un ticket brouillon ou en attente peut être supprimé.');
        }

        $ticket->delete();
    }

    /**
     * @return string[] stock error messages
     */
    public function stockErrors(TicketComptoir $ticket, int $serviceId): array
    {
        $errors = [];
        $ticket->loadMissing('Elements.Produit');

        foreach ($ticket->Elements as $el) {
            $produit = $el->Produit;
            if (!$produit) {
                $errors[] = 'Produit manquant sur une ligne.';
                continue;
            }
            $qteDemandee = (float) $el->quantite;
            $dispo = (float) ($produit->qte ?? 0);
            if ($qteDemandee > $dispo + TicketMoney::tolerance()) {
                $errors[] = sprintf(
                    '« %s » — stock disponible : %s | demandé : %s',
                    $produit->designation,
                    number_format($dispo, 2, ',', ' '),
                    number_format($qteDemandee, 2, ',', ' ')
                );
            }
        }

        return $errors;
    }

    /**
     * Pay ticket: multi-tender, stock, idempotent.
     *
     * @param  array  $paiements  modepaie_id, montant, montant_recu?, rendu?, reference_paiement?
     */
    public function payer(
        TicketComptoir $ticket,
        User $user,
        array $paiements,
        ?float $expectedTtc = null
    ): TicketComptoir {
        return DB::transaction(function () use ($ticket, $user, $paiements, $expectedTtc) {
            $ticket = TicketComptoir::lockForUpdate()->with(['Elements', 'SessionCaisse'])->findOrFail($ticket->id);

            // Idempotent success
            if ($ticket->statut === TicketComptoir::STATUT_PAYE && $ticket->stock_moved_at) {
                return $ticket->fresh(['Elements', 'Reglements.ModePaie', 'Contribuable', 'User']);
            }

            if (!$ticket->isEditable()) {
                throw new RuntimeException('Ce ticket ne peut pas être encaissé (statut: ' . $ticket->statut . ').');
            }

            $session = $ticket->SessionCaisse;
            if (!$session || !$session->allowsSales()) {
                throw new RuntimeException('La caisse n\'est pas ouverte aux ventes.');
            }
            if (!$session->service_id) {
                throw new RuntimeException('Dépôt (service) manquant sur la session — vente impossible.');
            }

            $this->recomputeTotals($ticket);
            $ticket->refresh();

            if ($ticket->Elements()->count() === 0) {
                throw new RuntimeException('Ticket sans lignes.');
            }

            $serverTtc = (float) $ticket->montant_ttc;
            if ($expectedTtc !== null && !TicketMoney::nearlyEqual($expectedTtc, $serverTtc)) {
                throw new RuntimeException(
                    'Montant TTC client (' . $expectedTtc . ') différent du serveur (' . $serverTtc . ').'
                );
            }

            $sumPay = 0.0;
            foreach ($paiements as $p) {
                $sumPay += (float) ($p['montant'] ?? 0);
            }
            $sumPay = round($sumPay, 2);
            if (!TicketMoney::nearlyEqual($sumPay, $serverTtc)) {
                throw new RuntimeException(
                    'La somme des paiements (' . $sumPay . ') doit égaler le TTC (' . $serverTtc . ').'
                );
            }

            $stockErrors = $this->stockErrors($ticket, (int) $session->service_id);
            if (!empty($stockErrors)) {
                throw new RuntimeException('Stock insuffisant : ' . implode(' — ', $stockErrors));
            }

            // Persist payments
            $ticket->Reglements()->delete();
            $year = date('Y');
            $i = 0;
            foreach ($paiements as $p) {
                $modepaieId = (int) ($p['modepaie_id'] ?? 0);
                $mode = ModePaie::find($modepaieId);
                if (!$mode) {
                    throw new RuntimeException('Mode de paiement invalide.');
                }
                $montant = round((float) ($p['montant'] ?? 0), 2);
                if ($montant <= 0) {
                    continue;
                }
                $i++;
                $montantRecu = isset($p['montant_recu']) ? (float) $p['montant_recu'] : null;
                $rendu = isset($p['rendu']) ? (float) $p['rendu'] : null;
                if (strtoupper((string) $mode->code) === 'ESP' && $montantRecu !== null) {
                    $rendu = round(max(0, $montantRecu - $montant), 2);
                }

                ReglementTicket::create([
                    'code' => 'RTK-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT) . '/' . $year,
                    'ticket_comptoir_id' => $ticket->id,
                    'date' => $session->date_metier,
                    'modepaie_id' => $mode->id,
                    'mode' => $mode->libelle,
                    'montant' => $montant,
                    'montant_recu' => $montantRecu,
                    'rendu' => $rendu,
                    'reference_paiement' => $p['reference_paiement'] ?? null,
                    'users_id' => $user->id,
                ]);
            }

            // Stock movements — must not silent no-op
            foreach ($ticket->Elements as $el) {
                $qte = (float) $el->quantite;
                if ($qte <= 0) {
                    continue;
                }
                StockService::sortie(
                    (int) $el->produits_id,
                    (int) $session->service_id,
                    $qte,
                    'ticket_comptoir',
                    (int) $ticket->id,
                    'Vente comptoir ' . $ticket->code
                );
            }

            $ticket->update([
                'statut' => TicketComptoir::STATUT_PAYE,
                'stock_moved_at' => now(),
                'user_id' => $ticket->user_id ?: $user->id,
            ]);

            return $ticket->fresh(['Elements', 'Reglements.ModePaie', 'Contribuable', 'User']);
        });
    }

    public function toJsonArray(TicketComptoir $ticket): array
    {
        $ticket->loadMissing(['Elements', 'Contribuable', 'User', 'Reglements.ModePaie']);

        return [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'numero' => $ticket->numero,
            'statut' => $ticket->statut,
            'date_metier' => optional($ticket->date_metier)->format('Y-m-d'),
            'contribuable_id' => $ticket->contribuable_id,
            'client_libre' => $ticket->client_libre,
            'client_label' => optional($ticket->Contribuable)->assujeti ?: $ticket->client_libre,
            'user_id' => $ticket->user_id,
            'seller_label' => trim((optional($ticket->User)->prenom ?? '') . ' ' . (optional($ticket->User)->nom ?? '')),
            'remise' => (float) $ticket->remise,
            'montant_ht' => (float) $ticket->montant_ht,
            'montant_ttc' => (float) $ticket->montant_ttc,
            'stock_moved_at' => optional($ticket->stock_moved_at)->toIso8601String(),
            'lignes' => $ticket->Elements->map(function ($el) {
                return [
                    'id' => $el->id,
                    'produits_id' => $el->produits_id,
                    'designation' => $el->designation,
                    'quantite' => (float) $el->quantite,
                    'prix_unitaire' => (float) $el->prix_unitaire,
                    'tva_taux' => (float) $el->tva_taux,
                    'montant_ht' => (float) $el->montant_ht,
                    'montant_tva' => (float) $el->montant_tva,
                    'montant_ttc_ligne' => (float) $el->montant_ttc_ligne,
                ];
            })->values()->all(),
            'paiements' => $ticket->Reglements->map(function ($r) {
                return [
                    'id' => $r->id,
                    'modepaie_id' => $r->modepaie_id,
                    'mode' => $r->mode,
                    'montant' => (float) $r->montant,
                    'montant_recu' => $r->montant_recu !== null ? (float) $r->montant_recu : null,
                    'rendu' => $r->rendu !== null ? (float) $r->rendu : null,
                ];
            })->values()->all(),
        ];
    }
}
