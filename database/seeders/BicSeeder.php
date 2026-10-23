<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BicSeeder extends Seeder
{
    /**
     * Catégories officielles PSVB (Prélèvement Spécifique sur les Ventes de Biens) - DGI Burkina Faso
     */
    public function run()
    {
        $now = Carbon::now();

        $bics = [
            [
                'code'     => 'A',
                'intitule' => 'PSVB Droit commun (2%)',
                'taux'     => 2.0,
            ],
            [
                'code'     => 'B',
                'intitule' => 'PSVB Dérogation (1%)',
                'taux'     => 1.0,
            ],
            [
                'code'     => 'C',
                'intitule' => 'PSVB Dérogation spéciale (0.2%)',
                'taux'     => 0.2,
            ],
            [
                'code'     => 'D',
                'intitule' => 'PSVB Réservé / Exonéré (0%)',
                'taux'     => 0.0,
            ],
        ];

        foreach ($bics as $bic) {
            DB::table('bics')->updateOrInsert(
                ['code' => $bic['code']],
                array_merge($bic, [
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }
    }
}
