<?php

namespace App\Http\Controllers;

use App\Models\Entete;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnteteController extends Controller
{
    /**
     * L'entête est un singleton : on affiche directement le formulaire
     * de l'unique enregistrement (créé automatiquement s'il n'existe pas).
     */
    public function index()
    {
        $entete = Entete::singleton();
        return view('pages.entete.index', compact('entete'));
    }

    /**
     * Pas de création possible : un seul entête est autorisé.
     */
    public function create()
    {
        return redirect()->route('module_entete.index');
    }

    /**
     * Pas de création possible : on redirige vers la mise à jour du singleton.
     */
    public function store(Request $request)
    {
        return $this->update($request, Entete::singleton()->id);
    }

    /**
     * Pas de vue de détail séparée : tout se passe sur index().
     */
    public function show(Entete $entete)
    {
        return redirect()->route('module_entete.index');
    }

    /**
     * Pas de vue d'édition séparée : tout se passe sur index().
     */
    public function edit(String $id)
    {
        return redirect()->route('module_entete.index');
    }

    /**
     * Met à jour l'unique entête de la société.
     */
    public function update(Request $request, String $id)
    {
        if ($request->filled('infos_cadastrales')) {
            $raw = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->infos_cadastrales));
            if (strlen($raw) === 11) {
                $request->merge([
                    'infos_cadastrales' => substr($raw, 0, 4) . '-' . substr($raw, 4, 3) . '-' . substr($raw, 7, 4)
                ]);
            }
        }

        $request->validate([
            'denomination' => 'required|string|max:255',
            'ifu' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'infos_cadastrales' => [
                'nullable',
                'string',
                'regex:/^[A-Za-z0-9]{4}-[A-Za-z0-9]{3}-[A-Za-z0-9]{4}$/'
            ],
            'sfe_bridge_url' => 'nullable|url|max:255',
            'sfe_bridge_timeout' => 'nullable|integer|min:1|max:120',
        ], [
            'infos_cadastrales.regex' => 'Le format de l\'adresse cadastrale doit être strictement : XXXX-YYY-ZZZZ (ex: 0012-004-0018).',
        ]);
        $entete = Entete::singleton();

        $entete->update([
            'denomination' => $request->denomination,
            'activite' => $request->activite,
            'postale' => $request->postale,
            'infos_cadastrales' => strtoupper((string) $request->infos_cadastrales),
            'telephone' => $request->telephone,
            'fax' => $request->fax,
            'email' => $request->email,
            'pied_page' => $request->pied_page,
            'ifu' => $request->ifu,
            'rccm' => $request->rccm,
            'regime_fiscal' => $request->regime_fiscal,
            'division_fiscal' => $request->division_fiscal,
            'identifiant_sfe' => $request->identifiant_sfe,
            'comptes_bancaires' => $request->comptes_bancaires,
            'sfe_company_address' => $request->sfe_company_address,
        ]);

        if ($request->hasFile('logo')) {
            $entete->logo = $request->logo->store('images', 'public');
            $entete->save();
        }

        \App\Models\AuditLog::enregistrer('UPDATE', 'PARAMETRES', "Mise à jour des paramètres de l'entreprise ({$entete->denomination})");

        smilify('success', 'Paramètres de l\'entreprise mis à jour avec succès !');
        return redirect()->route('module_entete.index');
    }

    /**
     * Interroge directement le boîtier MCF physique pour extraire l'IFU et le NIM
     * et pré-remplir automatiquement les informations de l'entreprise.
     */
    public function recupererInfosMcf(\App\Services\Mcf\McfSerialDriver $driver)
    {
        try {
            $etat = $driver->lireEtat();
            $nim = trim($etat['nim'] ?? '');
            $ifu = trim($etat['ifu'] ?? '');

            if (!$nim && !$ifu) {
                smilify('error', 'Impossible de lire les informations du boîtier MCF. Vérifiez le câble USB.');
                return redirect()->route('module_entete.index');
            }

            $entete = Entete::singleton();
            $entete->update([
                'nim_mcf' => $nim ?: $entete->nim_mcf,
                'ifu'     => $ifu ?: $entete->ifu,
            ]);

            \App\Models\AuditLog::enregistrer('UPDATE', 'PARAMETRES', "Synchronisation IFU/NIM depuis le boîtier MCF (IFU: {$ifu}, NIM: {$nim})");

            smilify('success', "Informations synchronisées depuis le MCF avec succès : IFU [{$ifu}] et NIM [{$nim}].");
        } catch (\Throwable $e) {
            report($e);
            smilify('error', 'Erreur de lecture MCF : ' . $e->getMessage());
        }

        return redirect()->route('module_entete.index');
    }

    /**
     * La suppression de l'entête est interdite : un seul enregistrement
     * doit toujours exister pour la facturation.
     */
    public function destroy(String $id)
    {
        smilify('error', "L'entête de la société ne peut pas être supprimée.");
        return redirect()->route('module_entete.index');
    }
}
