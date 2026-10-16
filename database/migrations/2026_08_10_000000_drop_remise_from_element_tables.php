<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropRemiseFromElementTables extends Migration
{
    /**
     * Run the migrations.
     *
     * Supprime la colonne `remise` des tables `element_factures` et
     * `element_facture_proformas`.
     *
     * @return void
     */
    public function up()
    {
        foreach (['element_factures', 'element_facture_proformas'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'remise')) {
                DB::statement("ALTER TABLE `{$table}` DROP COLUMN `remise`");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (['element_factures', 'element_facture_proformas'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'remise')) {
                DB::statement("ALTER TABLE `{$table}` ADD COLUMN `remise` FLOAT NULL");
            }
        }
    }
}
