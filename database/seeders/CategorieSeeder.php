<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['code' => 'CC', 'libelle' => 'Client Comptant'],
            ['code' => 'PM', 'libelle' => 'Personne morale'],
            ['code' => 'PP', 'libelle' => 'Personne Physique'],
            ['code' => 'PC', 'libelle' => 'Personne Physique Commerçant'],
        ];

        foreach ($categories as $categorie) {
            DB::table('categories')->updateOrInsert(
                ['code' => $categorie['code']],
                $categorie
            );
        }
    }
}
