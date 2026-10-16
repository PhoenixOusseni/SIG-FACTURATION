<?php

namespace App\Http\Controllers\Comptoir;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\ReglementTicket;
use App\Models\SessionCaisse;
use App\Models\TicketComptoir;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardComptoirController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isSuperviseurComptoir()) {
            abort(403, 'Tableau de bord réservé aux superviseurs comptoir.');
        }

        $paidStatuts = [TicketComptoir::STATUT_PAYE, TicketComptoir::STATUT_CONVERTI];

        $today = Carbon::today();
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        // ── KPI du jour ──────────────────────────────────────────
        $ticketsJour = TicketComptoir::whereDate('date_metier', $today)->get();
        $ticketsJourPayes = $ticketsJour->whereIn('statut', $paidStatuts);
        $caJour = (float) $ticketsJourPayes->sum('montant_ttc');
        $nbTicketsJour = $ticketsJour->count();
        $nbTicketsPayesJour = $ticketsJourPayes->count();
        $panierMoyenJour = $nbTicketsPayesJour > 0 ? $caJour / $nbTicketsPayesJour : 0.0;

        // ── KPI du mois ──────────────────────────────────────────
        $caMois = (float) TicketComptoir::whereBetween('date_metier', [$startMonth->toDateString(), $endMonth->toDateString()])
            ->whereIn('statut', $paidStatuts)
            ->sum('montant_ttc');

        $nbTicketsMoisPayes = TicketComptoir::whereBetween('date_metier', [$startMonth->toDateString(), $endMonth->toDateString()])
            ->whereIn('statut', $paidStatuts)
            ->count();

        // ── Caisses / sessions actives ───────────────────────────
        $sessionsActives = SessionCaisse::whereIn('statut', [SessionCaisse::STATUT_OUVERTE, SessionCaisse::STATUT_EN_ARRET])->count();
        $fondEngage = (float) SessionCaisse::whereIn('statut', [SessionCaisse::STATUT_OUVERTE, SessionCaisse::STATUT_EN_ARRET])->sum('fond_initial');
        $nombreCaisses = Caisse::where('active', true)->count();

        // ── Encaissements du jour par mode de paiement ───────────
        $encaissementsParMode = ReglementTicket::whereDate('date', $today)
            ->select('mode', DB::raw('SUM(montant) as total'))
            ->groupBy('mode')
            ->pluck('total', 'mode')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // ── Répartition des tickets par statut (mois courant) ────
        $statutsCount = TicketComptoir::whereBetween('date_metier', [$startMonth->toDateString(), $endMonth->toDateString()])
            ->select('statut', DB::raw('count(*) as nb'))
            ->groupBy('statut')
            ->pluck('nb', 'statut');

        $nbBrouillon = (int) ($statutsCount[TicketComptoir::STATUT_BROUILLON] ?? 0);
        $nbEnAttente = (int) ($statutsCount[TicketComptoir::STATUT_EN_ATTENTE] ?? 0);
        $nbPaye = (int) ($statutsCount[TicketComptoir::STATUT_PAYE] ?? 0);
        $nbAnnule = (int) ($statutsCount[TicketComptoir::STATUT_ANNULE] ?? 0);
        $nbConverti = (int) ($statutsCount[TicketComptoir::STATUT_CONVERTI] ?? 0);
        $nbTicketsMois = $nbBrouillon + $nbEnAttente + $nbPaye + $nbAnnule + $nbConverti;

        // ── Évolution du CA comptoir — 14 derniers jours ─────────
        $debut = Carbon::now()->subDays(13)->startOfDay();
        $fin = Carbon::now()->endOfDay();

        $caParJour = TicketComptoir::whereBetween('date_metier', [$debut->toDateString(), $fin->toDateString()])
            ->whereIn('statut', $paidStatuts)
            ->select('date_metier', DB::raw('SUM(montant_ttc) as total'), DB::raw('COUNT(*) as nb'))
            ->groupBy('date_metier')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date_metier)->format('Y-m-d'));

        $labelsJours = [];
        $evolutionCaJour = [];
        $evolutionNbTickets = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i);
            $row = $caParJour->get($d->format('Y-m-d'));
            $labelsJours[] = $d->format('d/m');
            $evolutionCaJour[] = $row ? round((float) $row->total, 2) : 0;
            $evolutionNbTickets[] = $row ? (int) $row->nb : 0;
        }

        // ── Top produits vendus (mois courant) ───────────────────
        $topProduits = DB::table('element_ticket_comptoirs as e')
            ->join('ticket_comptoirs as t', 'e.ticket_comptoir_id', '=', 't.id')
            ->whereIn('t.statut', $paidStatuts)
            ->whereBetween('t.date_metier', [$startMonth->toDateString(), $endMonth->toDateString()])
            ->select(
                'e.designation',
                DB::raw('SUM(e.quantite) as qte'),
                DB::raw('SUM(e.montant_ttc_ligne) as ca')
            )
            ->groupBy('e.designation')
            ->orderByDesc('ca')
            ->limit(8)
            ->get();

        // ── CA par caisse (mois courant) ─────────────────────────
        $caParCaisse = TicketComptoir::join('caisses', 'ticket_comptoirs.caisse_id', '=', 'caisses.id')
            ->whereIn('ticket_comptoirs.statut', $paidStatuts)
            ->whereBetween('ticket_comptoirs.date_metier', [$startMonth->toDateString(), $endMonth->toDateString()])
            ->select('caisses.libelle', DB::raw('SUM(ticket_comptoirs.montant_ttc) as total'))
            ->groupBy('caisses.libelle')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'libelle')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // ── Derniers tickets ──────────────────────────────────────
        $derniersTickets = TicketComptoir::with(['Contribuable', 'User', 'Caisse'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // ── Sessions de caisse récentes ───────────────────────────
        $sessionsRecentes = SessionCaisse::with(['Caisse', 'OpenedBy'])
            ->withCount('Tickets')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $sessionsRecentes->each(function (SessionCaisse $session) use ($paidStatuts) {
            $session->montant_total = (float) $session->Tickets()->whereIn('statut', $paidStatuts)->sum('montant_ttc');
        });

        return view('pages.comptoir.dashboard', [
            'caJour' => $caJour,
            'caMois' => $caMois,
            'nbTicketsJour' => $nbTicketsJour,
            'nbTicketsPayesJour' => $nbTicketsPayesJour,
            'nbTicketsMoisPayes' => $nbTicketsMoisPayes,
            'panierMoyenJour' => $panierMoyenJour,
            'sessionsActives' => $sessionsActives,
            'fondEngage' => $fondEngage,
            'nombreCaisses' => $nombreCaisses,
            'encaissementsParMode' => $encaissementsParMode,
            'nbBrouillon' => $nbBrouillon,
            'nbEnAttente' => $nbEnAttente,
            'nbPaye' => $nbPaye,
            'nbAnnule' => $nbAnnule,
            'nbConverti' => $nbConverti,
            'nbTicketsMois' => $nbTicketsMois,
            'labelsJours' => $labelsJours,
            'evolutionCaJour' => $evolutionCaJour,
            'evolutionNbTickets' => $evolutionNbTickets,
            'topProduits' => $topProduits,
            'caParCaisse' => $caParCaisse,
            'derniersTickets' => $derniersTickets,
            'sessionsRecentes' => $sessionsRecentes,
        ]);
    }
}
