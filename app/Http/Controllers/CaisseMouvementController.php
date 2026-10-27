<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\Entete;
use App\Models\MouvementCaisse;
use App\Models\Reglement;
use App\Models\ReglementTicket;
use App\Models\SessionCaisse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaisseMouvementController extends Controller
{
    /**
     * Liste et journal complet des mouvements de caisse (Dépôts / Retraits FEC).
     */
    public function index(Request $request)
    {
        $query = MouvementCaisse::with(['Caisse', 'SessionCaisse', 'User'])
            ->orderBy('date_mouvement', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('type')) {
            $query->where('type', strtoupper($request->type));
        }

        if ($request->filled('caisse_id')) {
            $query->where('caisse_id', $request->caisse_id);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_mouvement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_mouvement', '<=', $request->date_fin);
        }

        $mouvements = $query->get();

        // Calculs de trésorerie en direct
        $today = Carbon::today()->toDateString();

        $totalDepotsGlobal = (float) MouvementCaisse::where('type', MouvementCaisse::TYPE_DEPOT)->sum('montant');
        $totalRetraitsGlobal = (float) MouvementCaisse::where('type', MouvementCaisse::TYPE_RETRAIT)->sum('montant');

        $totalDepotsJour = (float) MouvementCaisse::where('type', MouvementCaisse::TYPE_DEPOT)
            ->whereDate('date_mouvement', $today)->sum('montant');
        $totalRetraitsJour = (float) MouvementCaisse::where('type', MouvementCaisse::TYPE_RETRAIT)
            ->whereDate('date_mouvement', $today)->sum('montant');

        // Total des encaissements factures en espèces
        $totalEspecesFactures = (float) Reglement::where(function ($q) {
            $q->where('mode', 'like', '%Espèce%')->orWhere('mode', 'ESP');
        })->sum('montant');

        // Total des encaissements tickets POS en espèces
        $totalEspecesTickets = 0.0;
        if (\Illuminate\Support\Facades\Schema::hasTable('reglement_tickets')) {
            $totalEspecesTickets = (float) ReglementTicket::where(function ($q) {
                $q->where('mode', 'like', '%Espèce%')->orWhere('mode', 'ESP');
            })->sum('montant');
        }

        // Solde théorique total en caisse
        $soldeTheoriqueCaisse = max(0, round($totalDepotsGlobal + $totalEspecesFactures + $totalEspecesTickets - $totalRetraitsGlobal, 2));

        $caisses = Caisse::where('active', true)->orderBy('libelle')->get();
        $entete = Entete::singleton();

        return view('pages.caisse.index', compact(
            'mouvements',
            'totalDepotsGlobal',
            'totalRetraitsGlobal',
            'totalDepotsJour',
            'totalRetraitsJour',
            'totalEspecesFactures',
            'totalEspecesTickets',
            'soldeTheoriqueCaisse',
            'caisses',
            'entete'
        ));
    }

    /**
     * Enregistrer un dépôt ou un retrait de numéraire.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:DEPOT,RETRAIT',
            'montant' => 'required|numeric|gt:0',
            'motif' => 'required|string|max:255',
            'type_motif' => 'required|string|max:100',
            'beneficiaire' => 'nullable|string|max:255',
            'piece_justificative' => 'nullable|string|max:100',
            'caisse_id' => 'nullable|exists:caisses,id',
            'observation' => 'nullable|string|max:1000',
            'date_mouvement' => 'nullable|date',
        ]);

        $mouvement = DB::transaction(function () use ($data) {
            $type = strtoupper($data['type']);
            $prefix = ($type === 'DEPOT') ? 'DEP' : 'RET';
            $year = date('Y');

            $last = MouvementCaisse::where('type', $type)
                ->whereYear('date_mouvement', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNum = 1;
            if ($last && preg_match('/-(\d+)\//', $last->code, $m)) {
                $nextNum = intval($m[1]) + 1;
            }
            $code = $prefix . '-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT) . '/' . $year;

            // Session de caisse active associée
            $sessionId = null;
            if (!empty($data['caisse_id'])) {
                $openSession = SessionCaisse::where('caisse_id', $data['caisse_id'])
                    ->whereIn('statut', [SessionCaisse::STATUT_OUVERTE, SessionCaisse::STATUT_EN_ARRET])
                    ->latest('id')
                    ->first();
                $sessionId = $openSession?->id;
            }

            return MouvementCaisse::create([
                'code' => $code,
                'type' => $type,
                'montant' => (float) $data['montant'],
                'date_mouvement' => $data['date_mouvement'] ?? now(),
                'motif' => $data['motif'],
                'type_motif' => $data['type_motif'],
                'beneficiaire' => $data['beneficiaire'] ?? null,
                'piece_justificative' => $data['piece_justificative'] ?? null,
                'observation' => $data['observation'] ?? null,
                'caisse_id' => $data['caisse_id'] ?? null,
                'session_caisse_id' => $sessionId,
                'user_id' => auth()->id(),
            ]);
        });

        $msg = ($mouvement->isDepot())
            ? "Dépôt de numéraire de " . number_format($mouvement->montant, 0, ',', ' ') . " FCFA enregistré avec succès (N° {$mouvement->code})."
            : "Retrait de numéraire de " . number_format($mouvement->montant, 0, ',', ' ') . " FCFA enregistré avec succès (N° {$mouvement->code}).";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'mouvement' => $mouvement,
                'print_url' => route('module_caisse.print_recu', $mouvement->id),
            ]);
        }

        smilify('success', $msg);
        return redirect()->route('module_caisse.index')->with('print_mouvement_id', $mouvement->id);
    }

    /**
     * Impression officielle du Reçu / Bon de caisse (A5 ou ticket thermique).
     */
    public function printRecu($id)
    {
        $mouvement = MouvementCaisse::with(['Caisse', 'SessionCaisse', 'User'])->findOrFail($id);
        $entete = Entete::singleton();

        return view('pages.caisse.print_recu', compact('mouvement', 'entete'));
    }
}
