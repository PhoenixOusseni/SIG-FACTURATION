<?php

namespace Database\Seeders;

use App\Models\Produit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tvaByCode = DB::table('tvas')->pluck('id', 'code');

        $required = ['A', 'B', 'C', 'D'];
        foreach ($required as $code) {
            if (!isset($tvaByCode[$code])) {
                throw new \RuntimeException("TVA code {$code} must be seeded before produits.");
            }
        }

        $familleProduit = ['LOCBIE', 'IMPBIE', 'LOCSER', 'IMPSER'];

        $produits = [
            [
                'code' => 'PRD-001',
                'designation' => 'Ordinateur portable',
                'prix_unitaire' => 450000,
                'unite' => 'piece',
                'qte' => 0,
                'description' => 'Ordinateur portable bureautique',
                'famille_produit' => $familleProduit[0],
                'tva_id' => $tvaByCode['A'],
            ],
            [
                'code' => 'PRD-002',
                'designation' => 'Imprimante laser',
                'prix_unitaire' => 185000,
                'unite' => 'piece',
                'qte' => 0,
                'description' => 'Imprimante monochrome haute vitesse',
                'famille_produit' => $familleProduit[1],
                'tva_id' => $tvaByCode['C'],
            ],
            [
                'code' => 'PRD-003',
                'designation' => 'Ramette A4',
                'prix_unitaire' => 3500,
                'unite' => 'paquet',
                'qte' => 0,
                'description' => 'Papier A4 80g',
                'famille_produit' => $familleProduit[1],
                'tva_id' => $tvaByCode['B'],
            ],
            [
                'code' => 'PRD-004',
                'designation' => 'Encre noire',
                'prix_unitaire' => 22000,
                'unite' => 'cartouche',
                'qte' => 0,
                'description' => 'Cartouche encre compatible',
                'famille_produit' => $familleProduit[2],
                'tva_id' => $tvaByCode['D'],
            ],
            [
                'code' => 'PRD-005',
                'designation' => 'Clavier USB',
                'prix_unitaire' => 12000,
                'unite' => 'piece',
                'qte' => 0,
                'description' => 'Clavier filaire standard',
                'famille_produit' => $familleProduit[0],
                'tva_id' => $tvaByCode['A'],
            ],
        ];

        foreach ($produits as $produit) {
            Produit::updateOrCreate(
                ['code' => $produit['code']],
                $produit
            );
        }

        // Optional random data for local/dev usage.
        $factoryCount = (int) env('PRODUIT_FACTORY_COUNT', 0);
        if ($factoryCount > 0) {
            Produit::factory()->count($factoryCount)->create();
        }
    }
}
