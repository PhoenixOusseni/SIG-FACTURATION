<?php

namespace App\Http\Controllers\Comptoir;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\SessionCaisse;
use App\Services\Comptoir\CaisseSessionService;
use Illuminate\Http\Request;
use RuntimeException;

class SessionCaisseController extends Controller
{
    /** @var CaisseSessionService */
    protected $sessions;

    public function __construct(CaisseSessionService $sessions)
    {
        $this->sessions = $sessions;
    }

    public function ouvrir(Request $request)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request, 'Droits insuffisants pour ouvrir la caisse.');
        }

        $data = $request->validate([
            'caisse_id' => 'required|exists:caisses,id',
            'fond_initial' => 'required|numeric|min:0',
            'date_metier' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $caisse = Caisse::findOrFail($data['caisse_id']);
            $session = $this->sessions->ouvrir(
                $caisse,
                $request->user(),
                (float) $data['fond_initial'],
                $data['date_metier'] ?? null,
                $data['notes'] ?? null
            );
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage(), 422);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'session' => $this->sessionPayload($session->fresh(['Caisse', 'OpenedBy'])),
            ]);
        }

        return redirect()->route('module_comptoir.pos')->with('success', 'Caisse ouverte.');
    }

    public function arret(Request $request)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        $data = $request->validate([
            'session_id' => 'required|exists:session_caisses,id',
            'fond_declare' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $session = SessionCaisse::findOrFail($data['session_id']);
            $arret = $this->sessions->arreter(
                $session,
                $request->user(),
                isset($data['fond_declare']) ? (float) $data['fond_declare'] : null,
                $data['notes'] ?? null
            );
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage(), 422);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'arret' => $arret,
                'print_url' => route('print_arret_caisse', $arret->id),
            ]);
        }

        return redirect()->back()->with('success', 'Arrêt de caisse enregistré.');
    }

    public function reprise(Request $request)
    {
        if (!$request->user()->isCaissierComptoir()) {
            return $this->deny($request);
        }

        $data = $request->validate([
            'session_id' => 'required|exists:session_caisses,id',
        ]);

        try {
            $session = SessionCaisse::findOrFail($data['session_id']);
            $session = $this->sessions->reprendre($session, $request->user());
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage(), 422);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'session' => $this->sessionPayload($session->fresh(['Caisse', 'OpenedBy'])),
            ]);
        }

        return redirect()->route('module_comptoir.pos');
    }

    public function fermeture(Request $request)
    {
        if (!$request->user()->isSuperviseurComptoir()) {
            return $this->deny($request, 'Seul un superviseur peut fermer la caisse.');
        }

        $data = $request->validate([
            'session_id' => 'required|exists:session_caisses,id',
            'montant_declare' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $session = SessionCaisse::findOrFail($data['session_id']);
            $session = $this->sessions->fermer(
                $session,
                $request->user(),
                isset($data['montant_declare']) ? (float) $data['montant_declare'] : null,
                $data['notes'] ?? null
            );
        } catch (RuntimeException $e) {
            return $this->fail($request, $e->getMessage(), 422);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'session' => $this->sessionPayload($session),
                'print_url' => route('print_fermeture_caisse', $session->id),
            ]);
        }

        return redirect()->route('module_comptoir.pos')->with('success', 'Caisse fermée.');
    }

    public function couranteJson(Request $request)
    {
        $caisseId = $request->query('caisse_id');
        $session = $this->sessions->couranteForCaisse($caisseId ? (int) $caisseId : null);

        return response()->json([
            'success' => true,
            'session' => $session ? $this->sessionPayload($session) : null,
        ]);
    }

    public function printArret($id)
    {
        $arret = \App\Models\ArretCaisse::with(['SessionCaisse.Caisse', 'User'])->findOrFail($id);

        return view('pages.comptoir.arret.print', compact('arret'));
    }

    public function printFermeture($id)
    {
        $session = SessionCaisse::with(['Caisse', 'ClosedBy'])->findOrFail($id);

        return view('pages.comptoir.fermeture.print', compact('session'));
    }

    public function showOuvrir()
    {
        // Prefer POS + modal (no separate form page) for a no-reload cashier flow.
        return redirect()->route('module_comptoir.pos', ['ouvrir' => 1]);
    }

    protected function sessionPayload(SessionCaisse $session): array
    {
        $session->loadMissing(['Caisse', 'OpenedBy']);

        return [
            'id' => $session->id,
            'statut' => $session->statut,
            'date_metier' => optional($session->date_metier)->format('Y-m-d'),
            'fond_initial' => (float) $session->fond_initial,
            'dernier_numero' => (int) $session->dernier_numero,
            'caisse' => $session->Caisse ? [
                'id' => $session->Caisse->id,
                'code' => $session->Caisse->code,
                'libelle' => $session->Caisse->libelle,
            ] : null,
            'service_id' => $session->service_id,
            'opened_by' => $session->OpenedBy ? [
                'id' => $session->OpenedBy->id,
                'nom' => $session->OpenedBy->nom ?? $session->OpenedBy->name ?? '',
                'prenom' => $session->OpenedBy->prenom ?? '',
            ] : null,
        ];
    }

    protected function fail(Request $request, string $message, int $code = 422)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }

        return redirect()->back()->withInput()->with('form_error', $message);
    }

    protected function deny(Request $request, string $message = 'Accès refusé.')
    {
        return $this->fail($request, $message, 403);
    }
}
