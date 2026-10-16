<?php

namespace App\Services\Comptoir;

use App\Models\ElementFacture;
use App\Models\Facture;
use App\Models\Reglement;
use App\Models\TicketComptoir;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Convert N paid comptoir tickets into one classic Facture + règlements.
 *
 * HARD RULES (design D5 / D12):
 * - Never call FactureController::store
 * - Never call InvoiceSigningService / any App\Services\SFE\*
 * - Never move stock (already moved at ticket pay)
 * - Never alter ticket stock_moved_at
 * - Fail-closed if reste_a_payer > tolerance after payment transfer
 */
class TicketToFactureConverter
{
    /**
     * @param  int[]  $ticketIds
     * @param  array  $meta  contribuable_id, date?, typefacture_id?, modepaie_id?, service_id?, user_id?
     */
    public function convert(array $ticketIds, array $meta, User $user): Facture
    {
        $ticketIds = array_values(array_unique(array_map('intval', $ticketIds)));
        if (count($ticketIds) < 1) {
            throw new RuntimeException('Sélectionnez au moins un ticket payé.');
        }

        $contribuableId = (int) ($meta['contribuable_id'] ?? 0);
        if ($contribuableId <= 0) {
            throw new RuntimeException('Un client (contribuable) est obligatoire pour convertir en facture.');
        }

        return DB::transaction(function () use ($ticketIds, $meta, $user, $contribuableId) {
            $tickets = TicketComptoir::with(['Elements', 'Reglements.ModePaie', 'SessionCaisse'])
                ->whereIn('id', $ticketIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($tickets->count() !== count($ticketIds)) {
                throw new RuntimeException('Un ou plusieurs tickets sont introuvables.');
            }

            foreach ($tickets as $t) {
                if ($t->statut !== TicketComptoir::STATUT_PAYE) {
                    throw new RuntimeException(
                        'Le ticket ' . $t->code . ' n\'est pas au statut payé (actuel : ' . $t->statut . ').'
                    );
                }
                if ($t->facture_id) {
                    throw new RuntimeException('Le ticket ' . $t->code . ' est déjà lié à une facture.');
                }
            }

            $codes = $tickets->pluck('code')->all();
            $year = date('Y');
            $last = Facture::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
            $next = ($last ? intval(substr((string) $last->code, 4, 5)) : 0) + 1;
            $code = 'FAC-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT) . '/' . $year;

            $remise1 = round((float) $tickets->sum('remise'), 2);
            $serviceId = $meta['service_id']
                ?? optional($tickets->first()->SessionCaisse)->service_id
                ?? null;

            $facture = Facture::create([
                'code' => $code,
                'reference' => 'Comptoir: ' . implode(', ', $codes),
                'date' => $meta['date'] ?? now()->toDateString(),
                'statut' => 'en attente',
                'contribuable_id' => $contribuableId,
                'service_id' => $serviceId,
                'user_id' => $meta['user_id'] ?? $user->id,
                'typefacture_id' => $meta['typefacture_id'] ?? null,
                'modepaie_id' => $meta['modepaie_id'] ?? null,
                'bic_id' => null,
                'remise_1' => $remise1,
                'remise_2' => 0,
            ]);

            // Aggregate lines by (produits_id, prix_unitaire, tva_taux)
            $groups = [];
            foreach ($tickets as $t) {
                foreach ($t->Elements as $el) {
                    $key = $el->produits_id . '|' . number_format((float) $el->prix_unitaire, 2, '.', '')
                        . '|' . number_format((float) $el->tva_taux, 2, '.', '');
                    if (!isset($groups[$key])) {
                        $groups[$key] = [
                            'produits_id' => $el->produits_id,
                            'designation' => $el->designation,
                            'quantite' => 0.0,
                            'prix_unitaire' => (float) $el->prix_unitaire,
                        ];
                    }
                    $groups[$key]['quantite'] += (float) $el->quantite;
                    if (!$groups[$key]['designation'] && $el->designation) {
                        $groups[$key]['designation'] = $el->designation;
                    }
                }
            }

            foreach ($groups as $g) {
                $qte = round($g['quantite'], 3);
                $pu = (float) $g['prix_unitaire'];
                ElementFacture::create([
                    'facture_id' => $facture->id,
                    'produits_id' => $g['produits_id'],
                    'designation' => $g['designation'],
                    'quantite' => $qte,
                    'prix_unitaire' => $pu,
                    'montant_total' => round($qte * $pu, 2),
                ]);
            }

            // Transfer payments by mode label (classic Reglement.mode strings)
            $byMode = [];
            foreach ($tickets as $t) {
                foreach ($t->Reglements as $rt) {
                    $label = $this->modeLabelForClassicReglement($rt);
                    $byMode[$label] = ($byMode[$label] ?? 0) + (float) $rt->montant;
                }
            }

            $regIndex = 0;
            foreach ($byMode as $modeLabel => $montant) {
                $montant = round($montant, 2);
                if ($montant <= 0) {
                    continue;
                }
                $regIndex++;
                Reglement::create([
                    'code' => $this->nextReglementCode($year, $regIndex),
                    'date' => $facture->date,
                    'mode' => $modeLabel,
                    'montant' => $montant,
                    'reference_paiement' => null,
                    'observation' => 'Conversion comptoir: ' . implode(', ', $codes),
                    'facture_id' => $facture->id,
                    'users_id' => $user->id,
                ]);
            }

            $facture = $facture->fresh(['ElementFactures.Produit.tva', 'Bic', 'Reglements']);
            $reste = (float) $facture->reste_a_payer;

            if (abs($reste) > TicketMoney::tolerance()) {
                throw new RuntimeException(
                    'Conversion impossible : le net facture ne correspond pas aux encaissements comptoir. '
                    . 'Reste calculé : ' . number_format($reste, 2, ',', ' ')
                    . ' FCFA. Vérifiez les taux de TVA produits (les tickets utilisent un snapshot) puis réessayez.'
                );
            }

            $facture->update(['statut' => 'soldée']);

            // Mark tickets converted — do NOT touch stock_moved_at
            foreach ($tickets as $t) {
                $t->update([
                    'statut' => TicketComptoir::STATUT_CONVERTI,
                    'facture_id' => $facture->id,
                ]);
            }

            return $facture->fresh(['ElementFactures', 'Reglements', 'Contribuable']);
        });
    }

    protected function modeLabelForClassicReglement($rt): string
    {
        $code = strtoupper((string) optional($rt->ModePaie)->code);
        if ($code === 'ESP') {
            return 'Espèces';
        }
        if ($code === 'CHQ') {
            return 'Chèque';
        }
        if ($code === 'VIR') {
            return 'Virement';
        }

        return $rt->mode ?: (optional($rt->ModePaie)->libelle ?: 'Espèces');
    }

    /**
     * Generate REG codes; append index in same second to reduce collision inside one TX.
     */
    protected function nextReglementCode(string $year, int $offset = 1): string
    {
        $last = Reglement::whereYear('created_at', $year)->orderByDesc('id')->first();
        $next = ($last ? intval(substr((string) $last->code, 4, 5)) : 0) + $offset;

        return 'REG-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }
}
