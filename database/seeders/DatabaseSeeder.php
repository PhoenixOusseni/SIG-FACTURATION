<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            EnteteSeeder::class,
            CategorieSeeder::class,
            ContribuableSeeder::class,
            ServiceSeeder::class,
            TvaSeeder::class,
            BicSeeder::class,
            ProduitSeeder::class,
            FournisseurSeeder::class,
            TypefactureSeeder::class,
            ModePaieSeeder::class,
            CaisseSeeder::class,
        ]);
    }
}
