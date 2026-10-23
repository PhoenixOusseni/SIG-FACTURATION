<?php

namespace App\Http\Controllers;

use App\Models\Reglement;
use App\Models\Facture;
use App\Models\Entete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReglementController extends Controller
{
    /* ============================================================
     |  LISTE GLOBALE DES RÈGLEMENTS
     * ============================================================ */
    public function index()
    {
        $collection = Reglement::with(['Facture.Contribuable', 'User'])
            ->orderByDesc('id')
            ->get();

        // Liste des factures non soldées pour le modal "Nouveau règlement"
        $factures = Facture::with([
            'Contribuable',
            'ElementFactures.Produit.tva',
            'Bic',
            'Reglements',
        ])
            ->orderByDesc('id')
            ->get()
            ->filter(function ($f) {
                return $f->reste_a_payer > 0.009;
            })
            ->values();

        return view('pages.reglement.index', compact('collection', 'factures'));
    }

    /* ============================================================
     |  ENREGISTREMENT
     * ============================================================ */
    public function store(Request $request)
    {
        $request->validate([
            'facture_id'         => ['required', 'exists:factures,id'],
            'date'               => ['required', 'date'],
            'mode'               => ['required', 'string', 'max:50'],
            'montant'            => ['required', 'numeric', 'min:0.01'],
            'reference_paiement' => ['nullable', 'string', 'max:150'],
            'observation'        => ['nullable', 'string'],
        ]);

        $facture = Facture::findOrFail($request->facture_id);

        // Vérifier que le montant n'excède pas le reste à payer
        $reste = $facture->reste_a_payer;
        if ((float) $request->montant > $reste + 0.009) {
            smilify('error', 'Le montant saisi (' . number_format($request->montant, 2, ',', ' ') . ') dépasse le reste à payer (' . number_format($reste, 2, ',', ' ') . ' FCFA).');
            return redirect()->back()->withInput();
        }

        DB::transaction(function () use ($request, $facture) {
            Reglement::create([
                'code'               => $this->generateCode(),
                'date'               => $request->date,
                'mode'               => $request->mode,
                'montant'            => $request->montant,
                'reference_paiement' => $request->reference_paiement,
                'observation'        => $request->observation,
                'facture_id'         => $facture->id,
                'users_id'           => auth()->id(),
            ]);

            // Mise à jour du statut de la facture si soldée
            $factureFraiche = Facture::find($facture->id);
            if ($factureFraiche->reste_a_payer <= 0.009) {
                $factureFraiche->update(['statut' => 'soldée']);
            } elseif ($factureFraiche->montant_paye > 0) {
                $factureFraiche->update(['statut' => 'partiellement payée']);
            }
        });

        smilify('success', 'Règlement enregistré avec succès.');
        return redirect()->back();
    }

    /* ============================================================
     |  AFFICHAGE
     * ============================================================ */
    public function show($id)
    {
        $reglement = Reglement::with(['Facture.Contribuable', 'User'])->findOrFail($id);
        return view('pages.reglement.show', compact('reglement'));
    }

    /* ============================================================
     |  MISE À JOUR
     * ============================================================ */
    public function update(Request $request, $id)
    {
        $reglement = Reglement::findOrFail($id);

        $request->validate([
            'date'               => ['required', 'date'],
            'mode'               => ['required', 'string', 'max:50'],
            'montant'            => ['required', 'numeric', 'min:0.01'],
            'reference_paiement' => ['nullable', 'string', 'max:150'],
            'observation'        => ['nullable', 'string'],
        ]);

        $facture = $reglement->Facture;
        $autresReglements = $facture->Reglements()->where('id', '!=', $reglement->id)->sum('montant');
        $maxMontant = $facture->montant_total - $autresReglements;

        if ((float) $request->montant > $maxMontant + 0.009) {
            smilify('error', 'Le montant modifié dépasse le reste à payer (' . number_format($maxMontant, 2, ',', ' ') . ' FCFA).');
            return redirect()->back()->withInput();
        }

        $reglement->update([
            'date'               => $request->date,
            'mode'               => $request->mode,
            'montant'            => $request->montant,
            'reference_paiement' => $request->reference_paiement,
            'observation'        => $request->observation,
        ]);

        // Re-évaluer le statut
        $facture = Facture::find($facture->id);
        if ($facture->reste_a_payer <= 0.009) {
            $facture->update(['statut' => 'soldée']);
        } elseif ($facture->montant_paye > 0) {
            $facture->update(['statut' => 'partiellement payée']);
        } else {
            $facture->update(['statut' => 'en attente']);
        }

        smilify('success', 'Règlement mis à jour.');
        return redirect()->back();
    }

    /* ============================================================
     |  SUPPRESSION
     * ============================================================ */
    public function destroy($id)
    {
        $reglement = Reglement::findOrFail($id);
        $factureId = $reglement->facture_id;
        $reglement->delete();

        // Re-évaluer le statut de la facture
        $facture = Facture::find($factureId);
        if ($facture) {
            if ($facture->montant_paye <= 0) {
                $facture->update(['statut' => 'en attente']);
            } elseif ($facture->reste_a_payer > 0.009) {
                $facture->update(['statut' => 'partiellement payée']);
            } else {
                $facture->update(['statut' => 'soldée']);
            }
        }

        smilify('success', 'Règlement supprimé.');
        return redirect()->back();
    }

    /* ============================================================
     |  IMPRESSION (REÇU DE PAIEMENT)
     * ============================================================ */
    public function print($id)
    {
        $reglement = Reglement::with([
            'Facture.Contribuable.Categorie',
            'Facture.Service',
            'User',
        ])->findOrFail($id);

        $entete = Entete::first();
        $facture = $reglement->Facture;

        // Conversion en lettres (FR) — montant absolu
        $montantLettres = $this->numberToFrenchWords((int) round(abs($reglement->montant))) . ' francs CFA';

        return view('pages.reglement.print_reglement', compact('reglement', 'facture', 'entete', 'montantLettres'));
    }

    /* ============================================================
     |  SOLDER UNE FACTURE (créer un règlement = reste à payer)
     * ============================================================ */
    public function solder(Request $request, $factureId)
    {
        $facture = Facture::findOrFail($factureId);
        $reste = round($facture->reste_a_payer, 2);

        if ($reste <= 0.009) {
            smilify('error', 'Cette facture est déjà soldée.');
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

        $reglement = null;
        DB::transaction(function () use ($request, $facture, $reste, $montant, &$reglement) {
            $reglement = Reglement::create([
                'code'               => $this->generateCode(),
                'date'               => $request->date ?? now()->toDateString(),
                'mode'               => $request->mode,
                'montant'            => $montant,
                'reference_paiement' => $request->reference_paiement,
                'observation'        => $request->observation ?? 'Règlement de la facture',
                'facture_id'         => $facture->id,
                'users_id'           => auth()->id(),
            ]);

            // Statut recalculé : si on couvre le reste => soldée, sinon partiellement payée
            $nouveauStatut = ($montant >= $reste - 0.009) ? 'soldée' : 'partiellement payée';
            $facture->update(['statut' => $nouveauStatut]);
        });

        smilify('success', 'Règlement de ' . number_format($montant, 0, ',', ' ') . ' FCFA enregistré avec succès.');
        return redirect()->route('module_reglement.show', $reglement->id);
    }

    /* ============================================================
     |  HELPERS
     * ============================================================ */
    protected function generateCode(): string
    {
        $year = date('Y');
        $last = Reglement::whereYear('created_at', $year)->orderByDesc('id')->first();
        $next = ($last ? intval(substr($last->code, 4, 5)) : 0) + 1;
        return 'REG-' . str_pad($next, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }

    /**
     * Convertit un nombre entier en lettres françaises.
     */
    protected function numberToFrenchWords(int $number): string
    {
        if ($number === 0) return 'zéro';

        $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
                  'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
                  'dix-sept', 'dix-huit', 'dix-neuf'];
        $tens = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];

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
                        $base = $tens[$t];
                        $s .= $base . '-' . $units[10 + $u];
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

        $parts = [];
        $billions = intdiv($number, 1000000000);
        $number %= 1000000000;
        $millions = intdiv($number, 1000000);
        $number %= 1000000;
        $thousands = intdiv($number, 1000);
        $number %= 1000;
        $rest = $number;

        if ($billions > 0) $parts[] = $convertBelow1000($billions) . ' milliard' . ($billions > 1 ? 's' : '');
        if ($millions > 0) $parts[] = $convertBelow1000($millions) . ' million' . ($millions > 1 ? 's' : '');
        if ($thousands > 0) {
            $parts[] = ($thousands === 1 ? 'mille' : $convertBelow1000($thousands) . ' mille');
        }
        if ($rest > 0) $parts[] = $convertBelow1000($rest);

        return trim(implode(' ', $parts));
    }
}
