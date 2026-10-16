<?php

namespace Database\Seeders;

use App\Models\Typefacture;
use Illuminate\Database\Seeder;

class TypefactureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // FEC 05 §1.7 — codes métier. Bridge MCF: FV|FA|EV|EA (FT/ET mappés en FV/EV).
        $types = [
            ['code' => 'FV', 'libelle' => 'Facture de vente'],
            ['code' => 'FT', 'libelle' => 'Facture d\'acompte'],
            ['code' => 'EV', 'libelle' => 'Facture de vente à l\'exportation'],
            ['code' => 'ET', 'libelle' => 'Facture d\'acompte à l\'exportation'],
            ['code' => 'FA', 'libelle' => 'Facture d\'avoir'],
            ['code' => 'EA', 'libelle' => 'Facture d\'avoir à l\'exportation'],
        ];

        foreach ($types as $type) {
            Typefacture::firstOrCreate(
                ['code' => $type['code']],
                ['libelle' => $type['libelle']]
            );
        }
    }
}
