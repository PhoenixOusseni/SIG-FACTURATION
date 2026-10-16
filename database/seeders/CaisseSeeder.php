<?php

namespace Database\Seeders;

use App\Models\Caisse;
use App\Models\Service;
use Illuminate\Database\Seeder;

class CaisseSeeder extends Seeder
{
    public function run()
    {
        $service = Service::orderBy('id')->first();
        if (!$service) {
            return;
        }

        Caisse::firstOrCreate(
            ['code' => 'C01'],
            [
                'libelle' => 'Caisse principale',
                'service_id' => $service->id,
                'active' => true,
            ]
        );
    }
}
