<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Entete;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $tab       = $request->input('tab', 'all');
        $entite    = $request->input('entite');
        $action    = $request->input('action');
        $dateDebut = $request->input('date_debut');
        $dateFin   = $request->input('date_fin');
        $recherche = $request->input('q');

        $query = AuditLog::with('user')->orderBy('id', 'desc');

        if ($tab === 'factures') {
            $query->where('entite', 'FACTURE');
        } elseif ($tab === 'avoirs') {
            $query->where('entite', 'AVOIR');
        } elseif ($tab === 'rapports') {
            $query->where('entite', 'RAPPORT');
        } elseif ($tab === 'mcf') {
            $query->where('entite', 'MCF');
        } elseif ($entite) {
            $query->where('entite', strtoupper($entite));
        }

        if ($action) {
            $query->where('action', strtoupper($action));
        }

        if ($dateDebut) {
            $query->whereDate('created_at', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->whereDate('created_at', '<=', $dateFin);
        }

        if ($recherche) {
            $query->where(function ($q) use ($recherche) {
                $q->where('nom_utilisateur', 'like', "%{$recherche}%")
                  ->orWhere('details', 'like', "%{$recherche}%")
                  ->orWhere('entite_id', 'like', "%{$recherche}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();
        $totalLogs = AuditLog::count();
        $countFactures = AuditLog::where('entite', 'FACTURE')->count();
        $countAvoirs = AuditLog::where('entite', 'AVOIR')->count();
        $countRapports = AuditLog::where('entite', 'RAPPORT')->count();
        $countMcf = AuditLog::where('entite', 'MCF')->count();

        return view('pages.audit.index', compact(
            'logs', 'tab', 'entite', 'action', 'dateDebut', 'dateFin', 'recherche',
            'totalLogs', 'countFactures', 'countAvoirs', 'countRapports', 'countMcf'
        ));
    }

    public function imprimer(Request $request)
    {
        $entite    = $request->input('entite');
        $action    = $request->input('action');
        $dateDebut = $request->input('date_debut');
        $dateFin   = $request->input('date_fin');

        $query = AuditLog::with('user')->orderBy('id', 'desc');

        if ($entite) $query->where('entite', strtoupper($entite));
        if ($action) $query->where('action', strtoupper($action));
        if ($dateDebut) $query->whereDate('created_at', '>=', $dateDebut);
        if ($dateFin) $query->whereDate('created_at', '<=', $dateFin);

        $logs = $query->limit(500)->get();
        $enteteSoc = Entete::singleton();

        return view('pages.audit.print_audit', compact('logs', 'entiteSoc', 'dateDebut', 'dateFin'));
    }
}
