<?php

namespace Database\Seeders;

use App\Models\Entete;
use Illuminate\Database\Seeder;

class EnteteSeeder extends Seeder
{
    /**
     * L'entête est un singleton : on met à jour l'unique enregistrement
     * s'il existe déjà, sinon on le crée.
     */
    public function run()
    {
        $entete = Entete::first();

        $data = [
            'denomination' => 'SOCIETE TEST',
            'activite' => 'Commerce Général & Prestations',
            'postale' => '01 BP 0000 Ouagadougou 01',
            'infos_cadastrales' => 'Section 12, Parcelle 04, Lot 18',
            'telephone' => '+226 25 30 00 00',
            'fax' => null,
            'email' => 'contact@societetest.bf',
            'pied_page' => 'Merci de votre confiance. Facture certifiée conforme à la réglementation DGI.',
            'ifu' => '00000000A',
            'rccm' => 'BF OUA 2024 B 0001',
            'regime_fiscal' => 'RNI',
            'division_fiscal' => 'DME 04',
            'identifiant_sfe' => 'AB01710090',
            'port_com' => null,
            'mcf_baudrate' => 9600,
            'mcf_parite' => 'NONE',
            'nim_mcf' => 'EL02000012-1',
            'categorie_psvb' => 'A',
            'comptes_bancaires' => 'BF00-1234-5678-90',
            'sfe_company_address' => 'Ouagadougou, Secteur 15, Rue 15.102',
            'sfe_automatic_health_check' => true,
            'sfe_automatic_invoice_signing' => false,
        ];

        if ($entete) {
            $entete->update($data);
        } else {
            Entete::create($data);
        }
    }
}
