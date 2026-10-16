<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SplitRemiseOnBordereauLivraisons extends Migration
{
    /**
     * Remplace la colonne unique `remise` par `remise_1` et `remise_2`
     * (même convention que sur `factures` et `facture_proformas`).
     */
    public function up()
    {
        Schema::table('bordereau_livraisons', function (Blueprint $table) {
            $table->decimal('remise_1', 15, 2)->nullable()->after('remise');
            $table->decimal('remise_2', 15, 2)->nullable()->after('remise_1');
        });

        DB::table('bordereau_livraisons')->update([
            'remise_1' => DB::raw('remise'),
        ]);

        // Laravel 8 requires Doctrine DBAL to drop SQLite columns. Keeping the
        // legacy column in the isolated test schema does not affect application behavior.
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('bordereau_livraisons', function (Blueprint $table) {
                $table->dropColumn('remise');
            });
        }
    }

    public function down()
    {
        Schema::table('bordereau_livraisons', function (Blueprint $table) {
            $table->decimal('remise', 15, 2)->nullable();
        });

        DB::table('bordereau_livraisons')->update([
            'remise' => DB::raw('remise_1'),
        ]);

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('bordereau_livraisons', function (Blueprint $table) {
                $table->dropColumn(['remise_1', 'remise_2']);
            });
        }
    }
}
