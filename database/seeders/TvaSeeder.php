<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TvaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $tvas = [
            [
                'code'        => 'A',
                'intitule'    => 'Exonéré',
                'compte_tva'  => 44560,
                'taux'        => 0,
            ],
            [
                'code'        => 'B',
                'intitule'    => 'Taxable standard',
                'compte_tva'  => 44561,
                'taux'        => 18,
            ],
            [
                'code'        => 'C',
                'intitule'    => 'Exportation',
                'compte_tva'  => 44562,
                'taux'        => 0,
            ],
            [
                'code'        => 'D',
                'intitule'    => 'TVA Réduite',
                'compte_tva'  => 44563,
                'taux'        => 10,
            ],
            [
                'code'        => 'E',
                'intitule'    => 'Taxe Spécifique',
                'compte_tva'  => 44564,
                'taux'        => null,
            ],
        ];

        foreach ($tvas as $tva) {
            DB::table('tvas')->updateOrInsert(
                ['code' => $tva['code']],
                array_merge($tva, [
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }
    }
}
