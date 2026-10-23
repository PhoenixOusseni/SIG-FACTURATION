<?php

namespace Database\Seeders;

use App\Models\ModePaie;
use Illuminate\Database\Seeder;

class ModePaieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modes = [
            ['code' => 'ESP', 'libelle' => 'Espèce'],
            ['code' => 'CHQ', 'libelle' => 'Chèque'],
            ['code' => 'VIR', 'libelle' => 'Virement bancaire'],
            ['code' => 'CAR', 'libelle' => 'Carte bancaire'],
            ['code' => 'MOB', 'libelle' => 'Mobile Money (Orange/Moov/Telecel)'],
            ['code' => 'CRE', 'libelle' => 'Crédit / À terme'],
            ['code' => 'AUT', 'libelle' => 'Autre'],
        ];

        foreach ($modes as $mode) {
            ModePaie::updateOrCreate(
                ['code' => $mode['code']],
                ['libelle' => $mode['libelle']]
            );
        }
    }
}
