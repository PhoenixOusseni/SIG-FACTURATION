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
        $request->validate(['denomination'=>'required|string|max:255','ifu'=>'required|string|max:50','email'=>'nullable|email|max:255','sfe_bridge_url'=>'nullable|url|max:255','sfe_bridge_timeout'=>'nullable|integer|min:1|max:120']);
        $entete = Entete::singleton();

        $entete->update([
            'denomination' => $request->denomination,
            'activite' => $request->activite,
            'postale' => $request->postale,
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
            'sfe_bridge_url' => $request->sfe_bridge_url ?: config('sfe.url'),
            'sfe_bridge_timeout' => $request->sfe_bridge_timeout ?: config('sfe.timeout'),
            'sfe_automatic_health_check' => $request->boolean('sfe_automatic_health_check'),
            'sfe_automatic_invoice_signing' => $request->boolean('sfe_automatic_invoice_signing'),
            'sfe_company_address' => $request->sfe_company_address,
        ]);

        if ($request->hasFile('logo')) {
            $entete->logo = $request->logo->store('images', 'public');
            $entete->save();
        }

        smilify('success', 'Entête modifiée avec succès !');
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
