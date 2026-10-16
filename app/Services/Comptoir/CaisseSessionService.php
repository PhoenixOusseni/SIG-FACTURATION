<?php

namespace App\Services\Comptoir;

use App\Models\ArretCaisse;
use App\Models\Caisse;
use App\Models\SessionCaisse;
use App\Models\TicketComptoir;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CaisseSessionService
{
    /** @var ComptoirReportService */
    protected $reports;

    public function __construct(ComptoirReportService $reports)
    {
        $this->reports = $reports;
    }

    /**
     * Open a session. Same-day re-open continues max(numero) (D11).
     */
    public function ouvrir(Caisse $caisse, User $user, float $fondInitial, ?string $dateMetier = null, ?string $notes = null): SessionCaisse
    {
        if (!$caisse->active) {
            throw new RuntimeException('Cette caisse est inactive.');
        }
        if (!$caisse->service_id) {
            throw new RuntimeException('La caisse n\'a pas de dépôt (service) configuré.');
        }

        $existing = $caisse->sessionOuverte();
        if ($existing) {
            throw new RuntimeException('Une session est déjà ouverte ou en arrêt sur cette caisse.');
        }

        $date = $dateMetier ? Carbon::parse($dateMetier)->toDateString() : Carbon::today()->toDateString();

        $maxNumero = (int) TicketComptoir::where('caisse_id', $caisse->id)
            ->whereDate('date_metier', $date)
            ->max('numero');

        return SessionCaisse::create([
            'caisse_id' => $caisse->id,
            'service_id' => $caisse->service_id,
            'date_metier' => $date,
            'statut' => SessionCaisse::STATUT_OUVERTE,
            'fond_initial' => $fondInitial,
            'dernier_numero' => $maxNumero,
            'opened_by' => $user->id,
            'opened_at' => now(),
            'notes_ouverture' => $notes,
        ]);
    }

    /**
     * Shift stop: freeze shift-delta snapshot, block sales until reprise.
     */
    public function arreter(SessionCaisse $session, User $user, ?float $fondDeclare = null, ?string $notes = null): ArretCaisse
    {
        if (!$session->isOuverte()) {
            throw new RuntimeException('Seule une session ouverte peut être mise en arrêt.');
        }

        $drafts = $session->Tickets()
            ->whereIn('statut', [TicketComptoir::STATUT_BROUILLON, TicketComptoir::STATUT_EN_ATTENTE])
            ->count();
        if ($drafts > 0) {
            throw new RuntimeException(
                'Impossible d\'arrêter la caisse : ' . $drafts . ' ticket(s) brouillon ou en attente. Réglez, annulez ou finalisez-les.'
            );
        }

        return DB::transaction(function () use ($session, $user, $fondDeclare, $notes) {
            $session = SessionCaisse::lockForUpdate()->findOrFail($session->id);
            if (!$session->isOuverte()) {
                throw new RuntimeException('Seule une session ouverte peut être mise en arrêt.');
            }

            list($windowStart, $exclusiveStart) = $this->reports->shiftWindowBounds($session);
            $arreteAt = now();
            $snapshot = $this->reports->shiftDelta($session, $windowStart, $arreteAt, $exclusiveStart);

            $arret = ArretCaisse::create([
                'session_caisse_id' => $session->id,
                'user_id' => $user->id,
                'arrete_at' => $arreteAt,
                'montant_ventes_ttc' => $snapshot['montant_ventes_ttc'],
                'nombre_tickets' => $snapshot['nombre_tickets'],
                'montant_annulations' => $snapshot['montant_annulations'],
                'detail_modes' => $snapshot['detail_modes'],
                'detail_articles' => $snapshot['detail_articles'],
                'fond_theorique' => $snapshot['fond_theorique'],
                'fond_declare' => $fondDeclare,
                'ecart' => $fondDeclare !== null
                    ? round($fondDeclare - $snapshot['fond_theorique'], 2)
                    : null,
                'notes' => $notes,
            ]);

            $session->update(['statut' => SessionCaisse::STATUT_EN_ARRET]);

            return $arret;
        });
    }

    public function reprendre(SessionCaisse $session, User $user): SessionCaisse
    {
        if (!$session->isEnArret()) {
            throw new RuntimeException('Seule une session en arrêt peut être reprise.');
        }

        $session->update(['statut' => SessionCaisse::STATUT_OUVERTE]);

        return $session->fresh();
    }

    /**
     * Day close with immutable fermeture snapshot.
     */
    public function fermer(SessionCaisse $session, User $user, ?float $montantDeclare = null, ?string $notes = null): SessionCaisse
    {
        if ($session->isFermee()) {
            throw new RuntimeException('Cette session est déjà fermée.');
        }

        $drafts = $session->Tickets()
            ->whereIn('statut', [TicketComptoir::STATUT_BROUILLON, TicketComptoir::STATUT_EN_ATTENTE])
            ->count();
        if ($drafts > 0) {
            throw new RuntimeException(
                'Impossible de fermer la caisse : ' . $drafts . ' ticket(s) non finalisé(s).'
            );
        }

        return DB::transaction(function () use ($session, $user, $montantDeclare, $notes) {
            $session = SessionCaisse::lockForUpdate()->findOrFail($session->id);
            if ($session->isFermee()) {
                throw new RuntimeException('Cette session est déjà fermée.');
            }

            // If still open, optional auto-arrêt is not done — require ouverte or en_arret.
            $snapshot = $this->reports->fullSessionTotals($session);
            $fondTheo = $snapshot['fond_theorique'];

            $session->update([
                'statut' => SessionCaisse::STATUT_FERMEE,
                'closed_by' => $user->id,
                'closed_at' => now(),
                'fermeture_montant_ventes_ttc' => $snapshot['montant_ventes_ttc'],
                'fermeture_nombre_tickets' => $snapshot['nombre_tickets'],
                'fermeture_montant_annulations' => $snapshot['montant_annulations'],
                'fermeture_detail_modes' => $snapshot['detail_modes'],
                'fermeture_detail_articles' => $snapshot['detail_articles'],
                'fermeture_fond_theorique' => $fondTheo,
                'montant_fermeture_declare' => $montantDeclare,
                'fermeture_ecart' => $montantDeclare !== null
                    ? round($montantDeclare - $fondTheo, 2)
                    : null,
                'notes_fermeture' => $notes,
            ]);

            return $session->fresh();
        });
    }

    public function couranteForCaisse(?int $caisseId = null): ?SessionCaisse
    {
        $q = SessionCaisse::with(['Caisse', 'OpenedBy', 'Service'])
            ->whereIn('statut', [SessionCaisse::STATUT_OUVERTE, SessionCaisse::STATUT_EN_ARRET])
            ->orderByDesc('id');

        if ($caisseId) {
            $q->where('caisse_id', $caisseId);
        }

        return $q->first();
    }
}
