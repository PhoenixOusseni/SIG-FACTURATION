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
            ['code' => 'VIR', 'libelle' => 'Virement'],
        ];

        foreach ($modes as $mode) {
            ModePaie::firstOrCreate(
                ['code' => $mode['code']],
                ['libelle' => $mode['libelle']]
            );
        }
    }
}
