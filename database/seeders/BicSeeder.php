<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $bics = [
            [
                'code'     => 'A',
                'intitule' => 'PSVBB Exonéré',
                'taux'     => 0,
            ],
            [
                'code'     => 'B',
                'intitule' => 'PSVBB 5%',
                'taux'     => 5,
            ],
            [
                'code'     => 'C',
                'intitule' => 'PSVBB 10%',
                'taux'     => 10,
            ],
            [
                'code'     => 'D',
                'intitule' => 'PSVBB 15%',
                'taux'     => 15,
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
