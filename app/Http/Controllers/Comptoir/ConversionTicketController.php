<?php

namespace App\Http\Controllers\Comptoir;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\Contribuable;
use App\Models\TicketComptoir;
use App\Models\Typefacture;
use App\Models\ModePaie;
use App\Services\Comptoir\TicketToFactureConverter;
use Illuminate\Http\Request;
use RuntimeException;

class ConversionTicketController extends Controller
{
    /** @var TicketToFactureConverter */
    protected $converter;

    public function __construct(TicketToFactureConverter $converter)
    {
        $this->converter = $converter;
    }

    public function index(Request $request)
    {
        if (!$request->user()->isSuperviseurComptoir()) {
            abort(403, 'Conversion réservée aux superviseurs.');
        }

        $q = TicketComptoir::with(['Contribuable', 'User', 'Caisse', 'Reglements.ModePaie'])
            ->where('statut', TicketComptoir::STATUT_PAYE)
            ->orderByDesc('id');

        if ($request->filled('date_metier')) {
            $q->whereDate('date_metier', $request->date_metier);
        }
        if ($request->filled('caisse_id')) {
            $q->where('caisse_id', $request->caisse_id);
        }
        if ($request->filled('contribuable_id')) {
            $q->where('contribuable_id', $request->contribuable_id);
        }
        if ($request->filled('q')) {
            $term = $request->q;
            $q->where(function ($w) use ($term) {
                $w->where('code', 'like', '%' . $term . '%')
                    ->orWhere('client_libre', 'like', '%' . $term . '%');
            });
        }

        $tickets = $q->paginate(50)->appends($request->query());
        $contribuables = Contribuable::orderBy('assujeti')->get(['id', 'assujeti', 'ifu']);
        $typefactures = Typefacture::whereIn('code', ['FV', 'FT', 'EV', 'ET'])->orderBy('code')->get();
        $modepaies = ModePaie::orderBy('libelle')->get();
        $caisses = Caisse::orderBy('libelle')->get();

        return view('pages.comptoir.conversion.index', compact(
            'tickets',
            'contribuables',
            'typefactures',
            'modepaies',
            'caisses'
        ));
    }

    public function store(Request $request)
    {
        if (!$request->user()->isSuperviseurComptoir()) {
            return $this->deny($request, 'Conversion réservée aux superviseurs.');
        }

        $data = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:ticket_comptoirs,id',
            'contribuable_id' => 'required|exists:contribuables,id',
            'date' => 'nullable|date',
            'typefacture_id' => 'nullable|exists:typefactures,id',
            'modepaie_id' => 'nullable|exists:modepaies,id',
        ]);

        try {
            $facture = $this->converter->convert(
                $data['ticket_ids'],
                [
                    'contribuable_id' => $data['contribuable_id'],
                    'date' => $data['date'] ?? now()->toDateString(),
                    'typefacture_id' => $data['typefacture_id'] ?? null,
                    'modepaie_id' => $data['modepaie_id'] ?? null,
                ],
                $request->user()
            );
        } catch (RuntimeException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->back()->withInput()->with('form_error', $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'facture' => [
                    'id' => $facture->id,
                    'code' => $facture->code,
                    'statut' => $facture->statut,
                ],
                'redirect' => route('module_ordre_facture.show', $facture->id),
            ]);
        }

        if (function_exists('smilify')) {
            smilify('success', 'Facture ' . $facture->code . ' créée (soldée) à partir des tickets comptoir.');
        }

        return redirect()->route('module_ordre_facture.show', $facture->id);
    }

    protected function deny(Request $request, string $message = 'Accès refusé.')
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }
        abort(403, $message);
    }
}
