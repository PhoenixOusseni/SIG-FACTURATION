<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TvaSeeder extends Seeder
{
    /**
     * Groupes officiels de taxation TVA - Norme DGI Burkina Faso (SECeF ST 1.0)
     */
    public function run()
    {
        $now = Carbon::now();

        $tvas = [
            ['code' => 'A', 'intitule' => 'Exonéré', 'compte_tva' => 44560, 'taux' => 0.0],
            ['code' => 'B', 'intitule' => 'TVA taxable standard 18%', 'compte_tva' => 44561, 'taux' => 18.0],
            ['code' => 'C', 'intitule' => 'TVA taxable réduite 10%', 'compte_tva' => 44562, 'taux' => 10.0],
            ['code' => 'D', 'intitule' => 'Exportation de produits taxables', 'compte_tva' => 44563, 'taux' => 0.0],
            ['code' => 'E', 'intitule' => 'TVA régime dérogatoire 0%', 'compte_tva' => 44564, 'taux' => 0.0],
            ['code' => 'F', 'intitule' => 'TVA régime dérogatoire 18%', 'compte_tva' => 44565, 'taux' => 18.0],
            ['code' => 'G', 'intitule' => 'TVA régime dérogatoire 10%', 'compte_tva' => 44566, 'taux' => 10.0],
            ['code' => 'H', 'intitule' => 'Régime synthétique', 'compte_tva' => 44567, 'taux' => 0.0],
            ['code' => 'I', 'intitule' => 'Consignation d\'emballage', 'compte_tva' => 44568, 'taux' => 0.0],
            ['code' => 'J', 'intitule' => 'Dépôts, garantie et caution', 'compte_tva' => 44569, 'taux' => 0.0],
            ['code' => 'K', 'intitule' => 'Débours', 'compte_tva' => 44570, 'taux' => 0.0],
            ['code' => 'L', 'intitule' => 'TDT - Taxe développement touristique 10%', 'compte_tva' => 44571, 'taux' => 10.0],
            ['code' => 'M', 'intitule' => 'Taxe de séjour hôtelier 10%', 'compte_tva' => 44572, 'taux' => 10.0],
            ['code' => 'N', 'intitule' => 'PBA - Droits fixes', 'compte_tva' => 44573, 'taux' => 0.0],
            ['code' => 'O', 'intitule' => 'Réservé O', 'compte_tva' => 44574, 'taux' => 0.0],
            ['code' => 'P', 'intitule' => 'Réservé P', 'compte_tva' => 44575, 'taux' => 0.0],
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
