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
            'activite' => 'Activité de test',
            'postale' => 'BP 0000 Ouagadougou',
            'telephone' => '00000000',
            'fax' => null,
            'email' => 'contact@societetest.test',
            'pied_page' => 'Merci de votre confiance.',
            'ifu' => '00000000A',
            'rccm' => 'RC-TEST-00001',
            'regime_fiscal' => 'RNI',
            'division_fiscal' => 'OUAGA 01',
            'identifiant_sfe' => null,
            'comptes_bancaires' => null,
        ];

        if ($entete) {
            $entete->update($data);
        } else {
            Entete::create($data);
        }
    }
}
