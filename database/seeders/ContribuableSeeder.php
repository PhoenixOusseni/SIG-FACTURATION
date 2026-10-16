<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContribuableSeeder extends Seeder
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
            throw new \RuntimeException('Categories CC/PM must be seeded before contribuables.');
        }

        $contribuables = [
            [
                'assujeti' => 'Mokutech',
                'adresse' => 'Ouagadougou, secteur 15',
                'telephone' => '75015560',
                'ifu' => '5012005R',
                'rccm' => 'C203350',
                'division_fiscal' => 'Division Centre',
                'regime_fiscal' => 'Reel normal',
                'adresse_cadas' => 'Parcelle 1500',
                'categories_id' => $catCc,
            ],
            [
                'assujeti' => 'SOTRACOM SARL',
                'adresse' => 'Bobo-Dioulasso, zone industrielle',
                'telephone' => '70223344',
                'ifu' => '00045678A',
                'rccm' => 'BF-BBO-01-2024-B12-00045',
                'division_fiscal' => 'Division Ouest',
                'regime_fiscal' => 'Reel simplifie',
                'adresse_cadas' => 'Parcelle B12',
                'categories_id' => $catCc,
            ],
            [
                'assujeti' => 'BURKI SERVICE PLUS',
                'adresse' => 'Ouagadougou, Koulouba',
                'telephone' => '76554433',
                'ifu' => '00099887Z',
                'rccm' => 'BF-OUA-01-2023-B11-00998',
                'division_fiscal' => 'Division Centre',
                'regime_fiscal' => 'Micro-entreprise',
                'adresse_cadas' => 'Parcelle KLB-22',
                'categories_id' => $catPm,
            ],
            [
                'assujeti' => 'AGRO SAHEL',
                'adresse' => 'Koudougou, route nationale 14',
                'telephone' => '71998877',
                'ifu' => '00123456M',
                'rccm' => 'BF-KDG-01-2022-B10-00111',
                'division_fiscal' => 'Division Centre-Ouest',
                'regime_fiscal' => 'Reel normal',
                'adresse_cadas' => 'Parcelle AGR-07',
                'categories_id' => $catPm,
            ],
        ];

        foreach ($contribuables as $contribuable) {
            DB::table('contribuables')->updateOrInsert(
                ['ifu' => $contribuable['ifu']],
                $contribuable
            );
        }
    }
}
