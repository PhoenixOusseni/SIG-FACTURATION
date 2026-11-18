<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UseDecimalQuantiteOnElementFactureAvoirs extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('element_facture_avoirs')) {
            return;
        }

        DB::statement('ALTER TABLE element_facture_avoirs MODIFY quantite DECIMAL(15,3) NULL');

        // Les quantités fractionnaires (2,5 / 3,25) étaient arrondies par la colonne entière.
        // Le montant de ligne a conservé la quantité réelle : on la restitue.
        $rows = DB::table('element_facture_avoirs')
            ->where('prix_unitaire', '>', 0)
            ->whereNotNull('montant_total')
            ->get(['id', 'quantite', 'prix_unitaire', 'montant_total']);

        foreach ($rows as $row) {
            $prix = (float) $row->prix_unitaire;
            $montant = (float) $row->montant_total;
            $stored = (float) $row->quantite;
            if (abs(($stored * $prix) - $montant) <= 0.01) {
                continue;
            }

            $expected = round($montant / $prix, 3);
            if ($expected <= 0) {
                continue;
            }

            if ((int) round($stored) === (int) round($expected)) {
                DB::table('element_facture_avoirs')
                    ->where('id', $row->id)
                    ->update(['quantite' => $expected]);
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('element_facture_avoirs')) {
            return;
        }

        DB::statement('ALTER TABLE element_facture_avoirs MODIFY quantite INT NULL');
    }
}
