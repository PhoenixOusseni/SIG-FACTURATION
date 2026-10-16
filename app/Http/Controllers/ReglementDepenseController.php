<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Models\Entete;
use App\Models\ReglementDepense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReglementDepenseController extends Controller
{
    public function index()
    {
        $collection = ReglementDepense::with(['depense.fournisseur', 'user'])
            ->orderByDesc('id')
            ->get();

        $depenses = Depense::with(['fournisseur', 'reglementDepenses'])
            ->orderByDesc('id')
            ->get()
            ->filter(fn($d) => $d->reste_a_payer > 0.009)
            ->values();

        return view('pages.depense.reglement_depense', compact('collection', 'depenses'));
    }

    public function store(Request $request, $depenseId)
    {
        $depense = Depense::findOrFail($depenseId);
        $reste   = $depense->reste_a_payer;

        $request->validate([
            'date'               => ['required', 'date'],
            'mode'               => ['required', 'string', 'max:50'],
            'montant'            => ['required', 'numeric', 'min:0.01'],
            'reference_paiement' => ['nullable', 'string', 'max:150'],
            'observation'        => ['nullable', 'string'],
        ]);

        if ((float) $request->montant > $reste + 0.009) {
            smilify('error', 'Le montant saisi (' . number_format($request->montant, 2, ',', ' ') . ') dépasse le reste à payer (' . number_format($reste, 2, ',', ' ') . ' FCFA).');
            return redirect()->back()->withInput();
        }

        DB::transaction(function () use ($request, $depense) {
            ReglementDepense::create([
                'code'               => $this->generateCode(),
                'date'               => $request->date,
                'mode'               => $request->mode,
                'montant'            => $request->montant,
                'reference_paiement' => $request->reference_paiement,
                'observation'        => $request->observation,
                'depense_id'         => $depense->id,
                'users_id'           => auth()->id(),
            ]);

            $depenseFraiche = Depense::find($depense->id);
            if ($depenseFraiche->reste_a_payer <= 0.009) {
                $depenseFraiche->update(['statut' => 'soldée']);
            } elseif ($depenseFraiche->montant_paye > 0) {
                $depenseFraiche->update(['statut' => 'partiellement payée']);
            }
        });

        smilify('success', 'Règlement enregistré avec succès.');
        return redirect()->back();
    }

    public function destroy($id)
    {
        $reglement = ReglementDepense::findOrFail($id);
        $depenseId = $reglement->depense_id;
        $reglement->delete();

        $depense = Depense::find($depenseId);
        if ($depense) {
            if ($depense->montant_paye <= 0) {
                $depense->update(['statut' => 'en attente']);
            } elseif ($depense->reste_a_payer > 0.009) {
                $depense->update(['statut' => 'partiellement payée']);
            } else {
                $depense->update(['statut' => 'soldée']);
            }
        }

        smilify('success', 'Règlement supprimé.');
        return redirect()->back();
    }

    public function solder(Request $request, $depenseId)
    {
        $depense = Depense::findOrFail($depenseId);
        $reste   = round($depense->reste_a_payer, 2);

        if ($reste <= 0.009) {
            smilify('error', 'Cette dépense est déjà soldée.');
            return redirect()->back();
        }

        $request->validate([
            'mode'               => ['required', 'string', 'max:50'],
            'montant'            => ['required', 'numeric', 'min:0.01', 'max:' . $reste],
            'date'               => ['nullable', 'date'],
            'reference_paiement' => ['nullable', 'string', 'max:150'],
            'observation'        => ['nullable', 'string'],
        ], [
            'montant.max' => "Le montant ne peut pas dépasser le reste à payer ({$reste} FCFA).",
        ]);

        $montant = round((float) $request->montant, 2);

        DB::transaction(function () use ($request, $depense, $reste, $montant) {
            ReglementDepense::create([
                'code'               => $this->generateCode(),
                'date'               => $request->date ?? now()->toDateString(),
                'mode'               => $request->mode,
                'montant'            => $montant,
                'reference_paiement' => $request->reference_paiement,
                'observation'        => $request->observation ?? 'Règlement de la dépense',
                'depense_id'         => $depense->id,
                'users_id'           => auth()->id(),
            ]);

            $nouveauStatut = ($montant >= $reste - 0.009) ? 'soldée' : 'partiellement payée';
            $depense->update(['statut' => $nouveauStatut]);
        });

        smilify('success', 'Règlement de ' . number_format($montant, 0, ',', ' ') . ' FCFA enregistré avec succès.');
        return redirect()->back();
    }

    public function printReglement($id)
    {
        $reglement = ReglementDepense::with(['depense.fournisseur', 'user'])->findOrFail($id);
        $depense   = $reglement->depense;
        $entete    = Entete::first();
        $montantLettres = $this->numberToFrenchWords((int) round($reglement->montant)) . ' francs CFA';

        return view('pages.depense.print_reglement_depense', compact('reglement', 'depense', 'entete', 'montantLettres'));
    }

    public function printDepense($depenseId)
    {
        $depense = Depense::with(['fournisseur', 'reglementDepenses.user'])->findOrFail($depenseId);
        $entete  = Entete::first();

        return view('pages.depense.print_depense', compact('depense', 'entete'));
    }

    protected function generateCode(): string
    {
        $year = date('Y');
        $last = ReglementDepense::whereYear('created_at', $year)->orderByDesc('id')->first();
        $next = ($last ? intval(substr($last->code, 3, 5)) : 0) + 1;
        return 'RD-' . str_pad($next, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }

    protected function numberToFrenchWords(int $number): string
    {
        if ($number === 0) return 'zéro';

        $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
                  'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
                  'dix-sept', 'dix-huit', 'dix-neuf'];
        $tens  = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];

        $convertBelow1000 = function ($n) use (&$convertBelow1000, $units, $tens) {
            $s = '';
            $h = intdiv($n, 100);
            $r = $n % 100;
            if ($h > 0) {
                $s .= ($h === 1 ? 'cent' : $units[$h] . ' cent') . ($h > 1 && $r === 0 ? 's' : '');
                if ($r > 0) $s .= ' ';
            }
            if ($r > 0) {
                if ($r < 20) {
                    $s .= $units[$r];
                } else {
                    $t = intdiv($r, 10);
                    $u = $r % 10;
                    if ($t === 7 || $t === 9) {
                        $s .= $tens[$t] . '-' . $units[10 + $u];
                    } elseif ($t === 8) {
                        $s .= 'quatre-vingt' . ($u === 0 ? 's' : '-' . $units[$u]);
                    } else {
                        $s .= $tens[$t];
                        if ($u === 1 && $t !== 8) $s .= ' et un';
                        elseif ($u > 0) $s .= '-' . $units[$u];
                    }
                }
            }
            return $s;
        };

        $parts    = [];
        $billions = intdiv($number, 1000000000);
        $number  %= 1000000000;
        $millions = intdiv($number, 1000000);
        $number  %= 1000000;
        $thousands = intdiv($number, 1000);
        $number  %= 1000;
        $rest     = $number;

        if ($billions  > 0) $parts[] = $convertBelow1000($billions)  . ' milliard' . ($billions  > 1 ? 's' : '');
        if ($millions  > 0) $parts[] = $convertBelow1000($millions)   . ' million'  . ($millions  > 1 ? 's' : '');
        if ($thousands > 0) $parts[] = ($thousands === 1 ? 'mille' : $convertBelow1000($thousands) . ' mille');
        if ($rest      > 0) $parts[] = $convertBelow1000($rest);

        return trim(implode(' ', $parts));
    }
}
