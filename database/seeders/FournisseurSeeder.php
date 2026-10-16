<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FournisseurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $catCc = DB::table('categories')->where('code', 'CC')->value('id');
        $catPm = DB::table('categories')->where('code', 'PM')->value('id');

        if (!$catCc || !$catPm) {
            throw new \RuntimeException('Categories CC/PM must be seeded before fournisseurs.');
        }

        $fournisseurs = [
            [
                'assujeti' => 'ETS NIKIEMA & FRERES',
                'adresse' => 'Ouagadougou, secteur 10',
                'adresse_localisation' => 'Avenue Kwame Nkrumah',
                'telephone' => '70112233',
                'ifu' => '00056789B',
                'rccm' => 'BF-OUA-01-2021-B12-00567',
                'division_fiscal' => 'Division Centre',
                'regime_fiscal' => 'Reel normal',
                'adresse_cadas' => 'Parcelle NKF-10',
                'categories_id' => $catCc,
            ],
            [
                'assujeti' => 'SOCIETE BURKINA EQUIPEMENT',
                'adresse' => 'Bobo-Dioulasso, zone industrielle',
                'adresse_localisation' => 'Route de Banfora',
                'telephone' => '76889900',
                'ifu' => '00067890C',
                'rccm' => 'BF-BBO-01-2020-B11-00678',
                'division_fiscal' => 'Division Ouest',
                'regime_fiscal' => 'Reel simplifie',
                'adresse_cadas' => 'Parcelle SBE-22',
                'categories_id' => $catCc,
            ],
            [
                'assujeti' => 'FASO MATERIAUX SARL',
                'adresse' => 'Ouagadougou, Patte d\'Oie',
                'adresse_localisation' => 'Boulevard Charles De Gaulle',
                'telephone' => '78445566',
                'ifu' => '00078901D',
                'rccm' => 'BF-OUA-01-2022-B10-00789',
                'division_fiscal' => 'Division Centre',
                'regime_fiscal' => 'Reel normal',
                'adresse_cadas' => 'Parcelle FMS-05',
                'categories_id' => $catPm,
            ],
            [
                'assujeti' => 'GENERAL DISTRIBUTION BF',
                'adresse' => 'Koudougou, centre-ville',
                'adresse_localisation' => 'Rue du Marche',
                'telephone' => '71223344',
                'ifu' => '00089012E',
                'rccm' => 'BF-KDG-01-2023-B09-00890',
                'division_fiscal' => 'Division Centre-Ouest',
                'regime_fiscal' => 'Micro-entreprise',
                'adresse_cadas' => 'Parcelle GDB-15',
                'categories_id' => $catPm,
            ],
            [
                'assujeti' => 'SAHEL TECH SUPPLY',
                'adresse' => 'Ouagadougou, Ouaga 2000',
                'adresse_localisation' => 'Avenue de la Liberte',
                'telephone' => '75667788',
                'ifu' => '00090123F',
                'rccm' => 'BF-OUA-01-2024-B08-00901',
                'division_fiscal' => 'Division Centre',
                'regime_fiscal' => 'Reel simplifie',
                'adresse_cadas' => 'Parcelle STS-33',
                'categories_id' => $catCc,
            ],
        ];

        foreach ($fournisseurs as $fournisseur) {
            DB::table('fournisseurs')->updateOrInsert(
                ['ifu' => $fournisseur['ifu']],
                $fournisseur
            );
        }
    }
}
