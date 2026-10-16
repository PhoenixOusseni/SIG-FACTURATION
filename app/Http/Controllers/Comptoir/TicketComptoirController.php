<?php

namespace App\Http\Controllers\Comptoir;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\Categorie;
use App\Models\ModePaie;
use App\Models\SessionCaisse;
use App\Models\TicketComptoir;
use App\Models\Tva;
use App\Services\Comptoir\CaisseSessionService;
use App\Services\Comptoir\TicketComptoirService;
use Illuminate\Http\Request;
use RuntimeException;

class TicketComptoirController extends Controller
{
    /** @var TicketComptoirService */
    protected $tickets;

    /** @var CaisseSessionService */
    protected $sessions;

    public function __construct(TicketComptoirService $tickets, CaisseSessionService $sessions)
    {
        $this->tickets = $tickets;
        $this->sessions = $sessions;
    }

    public function pos(Request $request)
    {
        if (!$request->user()->isCaissierComptoir()) {
            abort(403, 'Accès POS réservé aux caissiers.');
        }

        $session = $this->sessions->couranteForCaisse(null);
        $caisses = Caisse::where('active', true)->orderBy('libelle')->get();
        $categories = Categorie::orderBy('libelle')->get();
        $tvas = Tva::orderBy('code')->get();

        return view('pages.comptoir.pos', [
            'session' => $session,
            'caisses' => $caisses,
            'categories' => $categories,
            'tvas' => $tvas,
            'posConfig' => [
                'urls' => [
                    'meta' => route('module_comptoir.meta'),
                    'sessionCourante' => route('module_comptoir.session.courante'),
                    'produitsSearch' => route('module_comptoir.produits.search'),
                    'clientsSearch' => route('module_comptoir.contribuables.search'),
                    'ticketsStore' => route('module_comptoir.tickets.store'),
                    'ticketsEnAttente' => route('module_comptoir.tickets.en_attente'),
                    'ouvrir' => route('module_comptoir.ouvrir'),
                    'arret' => route('module_comptoir.arret'),
                    'reprise' => route('module_comptoir.reprise'),
                    'fermeture' => route('module_comptoir.fermeture'),
                ],
                'csrf' => csrf_token(),
                'userId' => $request->user()->id,
                'canFermer' => $request->user()->isSuperviseurComptoir(),
                'tolerance' => (float) config('comptoir.money_tolerance', 0.009),
            ],
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        $session = $this->sessions->couranteForCaisse($request->input('caisse_id'));
        if (!$session) {
            return $this->fail($request, 'Aucune session de caisse ouverte.');
        }

        try {
            $ticket = $this->tickets->createDraft($session, $request->user(), $request->all());
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'ticket' => $this->tickets->toJsonArray($ticket),
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        $ticket = TicketComptoir::findOrFail($id);

        try {
            $ticket = $this->tickets->updateCart($ticket, $request->all());
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'ticket' => $this->tickets->toJsonArray($ticket),
        ]);
    }

    public function show(Request $request, $id)
    {
        $ticket = TicketComptoir::findOrFail($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'ticket' => $this->tickets->toJsonArray($ticket),
            ]);
        }

        return view('pages.comptoir.tickets.show', compact('ticket'));
    }

    public function index(Request $request)
    {
        $ticketStatut = $request->filled('statut') ? $request->statut : null;

        $q = SessionCaisse::with(['Caisse', 'OpenedBy', 'Tickets' => function ($tq) use ($ticketStatut) {
                $tq->with(['Contribuable', 'User'])->orderByDesc('id');
                if ($ticketStatut) {
                    $tq->where('statut', $ticketStatut);
                }
            }])
            ->orderByDesc('date_metier')
            ->orderByDesc('id');

        if ($request->filled('date_metier')) {
            $q->whereDate('date_metier', $request->date_metier);
        }
        if ($request->filled('caisse_id')) {
            $q->where('caisse_id', $request->caisse_id);
        }
        if ($ticketStatut) {
            $q->whereHas('Tickets', function ($tq) use ($ticketStatut) {
                $tq->where('statut', $ticketStatut);
            });
        }

        $sessions = $q->paginate(15)->withQueryString();

        $sessions->getCollection()->each(function (SessionCaisse $session) {
            $session->montant_total = $session->Tickets->whereIn('statut', [
                TicketComptoir::STATUT_PAYE,
                TicketComptoir::STATUT_CONVERTI,
            ])->sum('montant_ttc');
        });

        $caisses = Caisse::orderBy('libelle')->get();
        $espMode = ModePaie::where('code', 'ESP')->first() ?: ModePaie::orderBy('id')->first();

        return view('pages.comptoir.tickets.index', [
            'sessions' => $sessions,
            'caisses' => $caisses,
            'espModeId' => optional($espMode)->id,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        try {
            $ticket = TicketComptoir::findOrFail($id);
            $code = $ticket->code;
            $this->tickets->supprimer($ticket);
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage(), 409);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Ticket ' . $code . ' supprimé.');
    }

    public function enAttente(Request $request)
    {
        $session = $this->sessions->couranteForCaisse($request->query('caisse_id'));
        if (!$session) {
            return response()->json(['success' => true, 'tickets' => []]);
        }

        $tickets = TicketComptoir::with(['Elements', 'Contribuable'])
            ->where('session_caisse_id', $session->id)
            ->where('statut', TicketComptoir::STATUT_EN_ATTENTE)
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($t) {
                return $this->tickets->toJsonArray($t);
            });

        return response()->json(['success' => true, 'tickets' => $tickets]);
    }

    public function mettreEnAttente(Request $request, $id)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        try {
            // sync cart first if body present
            $ticket = TicketComptoir::findOrFail($id);
            if ($request->has('lignes') || $request->has('remise')) {
                $ticket = $this->tickets->updateCart($ticket, $request->all());
            }
            $ticket = $this->tickets->mettreEnAttente($ticket);
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'ticket' => $this->tickets->toJsonArray($ticket),
        ]);
    }

    public function reprendre(Request $request, $id)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        $session = $this->sessions->couranteForCaisse(null);
        if (!$session) {
            return $this->fail($request, 'Aucune session ouverte.');
        }

        try {
            $ticket = TicketComptoir::findOrFail($id);
            $ticket = $this->tickets->reprendre($ticket, $session);
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'ticket' => $this->tickets->toJsonArray($ticket),
        ]);
    }

    public function validerStock(Request $request, $id)
    {
        $ticket = TicketComptoir::with('SessionCaisse')->findOrFail($id);
        $serviceId = optional($ticket->SessionCaisse)->service_id;
        if (!$serviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Dépôt manquant sur la session.',
            ], 422);
        }

        $errors = $this->tickets->stockErrors($ticket, (int) $serviceId);
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuffisant : ' . implode(' — ', $errors),
                'errors' => $errors,
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    public function payer(Request $request, $id)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        $data = $request->validate([
            'expected_ttc' => 'nullable|numeric',
            'paiements' => 'required|array|min:1',
            'paiements.*.modepaie_id' => 'required|exists:modepaies,id',
            'paiements.*.montant' => 'required|numeric|min:0.01',
            'paiements.*.montant_recu' => 'nullable|numeric|min:0',
            'paiements.*.rendu' => 'nullable|numeric|min:0',
            'paiements.*.reference_paiement' => 'nullable|string|max:100',
        ]);

        // optional cart sync before pay
        if ($request->has('lignes') || $request->has('remise') || $request->has('contribuable_id') || $request->has('client_libre')) {
            try {
                $ticket = TicketComptoir::findOrFail($id);
                $this->tickets->updateCart($ticket, $request->all());
            } catch (RuntimeException $e) {
                return $this->fail($request, $e->getMessage());
            }
        }

        try {
            $ticket = TicketComptoir::findOrFail($id);
            $ticket = $this->tickets->payer(
                $ticket,
                $request->user(),
                $data['paiements'],
                isset($data['expected_ttc']) ? (float) $data['expected_ttc'] : null
            );
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage(), 422);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'ticket' => $this->tickets->toJsonArray($ticket),
                'print_url' => route('print_ticket_comptoir', $ticket->id),
            ]);
        }

        return redirect()->back()->with('success', 'Ticket ' . $ticket->code . ' encaissé.');
    }

    public function annuler(Request $request, $id)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        try {
            $ticket = TicketComptoir::findOrFail($id);
            $ticket = $this->tickets->annuler($ticket, $request->user(), $request->input('motif'));
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage(), 409);
        }

        return response()->json([
            'success' => true,
            'ticket' => $this->tickets->toJsonArray($ticket),
        ]);
    }

    public function print($id)
    {
        $ticket = TicketComptoir::with(['Elements', 'Reglements.ModePaie', 'Contribuable', 'User', 'Caisse'])
            ->findOrFail($id);

        return view('pages.comptoir.tickets.print', compact('ticket'));
    }

    protected function fail(Request $request, string $message, int $code = 422)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }

        return redirect()->back()->with('form_error', $message);
    }

    protected function deny(Request $request, string $message = 'Accès refusé.')
    {
        return $this->fail($request, $message, 403);
    }
}
