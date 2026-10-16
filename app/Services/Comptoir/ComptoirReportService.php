<?php

namespace App\Services\Comptoir;

use App\Models\ModePaie;
use App\Models\SessionCaisse;
use App\Models\TicketComptoir;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ComptoirReportService
{
    /**
     * Start of current shift window.
     *
     * @return array{0: Carbon, 1: bool} [windowStart, exclusiveStart]
     *   - After an arrêt: exclusive (>) so tickets at arrete_at stay in previous shift
     *   - First shift (opened_at): inclusive (>=) so same-second sales after ouverture count
     */
    public function shiftWindowBounds(SessionCaisse $session): array
    {
        $lastArret = $session->Arrets()->orderByDesc('arrete_at')->first();
        if ($lastArret && $lastArret->arrete_at) {
            return [Carbon::parse($lastArret->arrete_at), true];
        }
        if ($session->opened_at) {
            return [Carbon::parse($session->opened_at), false];
        }

        return [Carbon::createFromTimestamp(0), false];
    }

    /**
     * @deprecated use shiftWindowBounds()
     */
    public function shiftWindowStart(SessionCaisse $session): Carbon
    {
        return $this->shiftWindowBounds($session)[0];
    }

    /**
     * Shift-delta using immutable stock_moved_at (D18).
     *
     * @param  bool  $exclusiveStart  true => stock_moved_at > start; false => >= start
     */
    public function shiftDelta(SessionCaisse $session, Carbon $windowStart, Carbon $windowEnd, bool $exclusiveStart = true): array
    {
        $q = TicketComptoir::with(['Elements', 'Reglements.ModePaie'])
            ->where('session_caisse_id', $session->id)
            ->whereIn('statut', [TicketComptoir::STATUT_PAYE, TicketComptoir::STATUT_CONVERTI])
            ->whereNotNull('stock_moved_at')
            ->where('stock_moved_at', '<=', $windowEnd);

        if ($exclusiveStart) {
            $q->where('stock_moved_at', '>', $windowStart);
        } else {
            $q->where('stock_moved_at', '>=', $windowStart);
        }

        $tickets = $q->get();

        return $this->aggregateTickets($session, $tickets);
    }

    public function fullSessionTotals(SessionCaisse $session): array
    {
        $tickets = TicketComptoir::with(['Elements', 'Reglements.ModePaie'])
            ->where('session_caisse_id', $session->id)
            ->whereIn('statut', [TicketComptoir::STATUT_PAYE, TicketComptoir::STATUT_CONVERTI])
            ->get();

        return $this->aggregateTickets($session, $tickets);
    }

    protected function aggregateTickets(SessionCaisse $session, Collection $tickets): array
    {
        $montantVentes = round((float) $tickets->sum('montant_ttc'), 2);
        $nombre = $tickets->count();

        $annulations = TicketComptoir::where('session_caisse_id', $session->id)
            ->where('statut', TicketComptoir::STATUT_ANNULE)
            ->sum('montant_ttc');

        $detailModes = [];
        $especes = 0.0;
        foreach ($tickets as $ticket) {
            foreach ($ticket->Reglements as $reg) {
                $code = optional($reg->ModePaie)->code ?: 'AUTRE';
                $lib = optional($reg->ModePaie)->libelle ?: $reg->mode ?: $code;
                if (!isset($detailModes[$code])) {
                    $detailModes[$code] = ['code' => $code, 'libelle' => $lib, 'montant' => 0.0];
                }
                $detailModes[$code]['montant'] = round($detailModes[$code]['montant'] + (float) $reg->montant, 2);
                if (strtoupper((string) $code) === 'ESP') {
                    $especes += (float) $reg->montant;
                }
            }
        }

        $detailArticles = [];
        foreach ($tickets as $ticket) {
            foreach ($ticket->Elements as $el) {
                $key = (string) $el->produits_id;
                if (!isset($detailArticles[$key])) {
                    $detailArticles[$key] = [
                        'produits_id' => $el->produits_id,
                        'designation' => $el->designation,
                        'quantite' => 0.0,
                        'montant_ttc' => 0.0,
                    ];
                }
                $detailArticles[$key]['quantite'] = round($detailArticles[$key]['quantite'] + (float) $el->quantite, 3);
                $detailArticles[$key]['montant_ttc'] = round(
                    $detailArticles[$key]['montant_ttc'] + (float) $el->montant_ttc_ligne,
                    2
                );
            }
        }

        $fondTheorique = round((float) $session->fond_initial + $especes, 2);

        return [
            'montant_ventes_ttc' => $montantVentes,
            'nombre_tickets' => $nombre,
            'montant_annulations' => round((float) $annulations, 2),
            'detail_modes' => array_values($detailModes),
            'detail_articles' => array_values($detailArticles),
            'fond_theorique' => $fondTheorique,
            'especes' => round($especes, 2),
        ];
    }

    public static function isEspecesModePaie(?ModePaie $mode): bool
    {
        return $mode && strtoupper((string) $mode->code) === 'ESP';
    }
}
