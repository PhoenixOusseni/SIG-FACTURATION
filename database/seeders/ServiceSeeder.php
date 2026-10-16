<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'code' => 'DEP-0001',
                'libelle' => 'Depot de maintenance',
                'description' => 'Depot de maintenance pour les produits',
            ],
            [
                'code' => 'DEP-0002',
                'libelle' => 'Depot principal',
                'description' => 'Depot principal pour les produits',
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
