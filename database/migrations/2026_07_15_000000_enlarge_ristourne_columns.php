<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnlargeRistourneColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * Augmente la taille des colonnes `ristourne` (passées de `float`/double(8,2)
     * à `decimal(15,2)`) pour accepter les montants élevés.
     *
     * @return void
     */
    public function up()
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (['facture_proformas', 'factures', 'bordereau_livraisons'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'ristourne')) {
                $this->alterDecimal($table, 'ristourne');
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
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (['facture_proformas', 'factures', 'bordereau_livraisons'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'ristourne')) {
                $this->alterFloat($table, 'ristourne');
            }
        }
    }

    protected function alterDecimal(string $table, string $column): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE DECIMAL(15,2) USING {$column}::DECIMAL(15,2)");
            return;
        }
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DECIMAL(15,2) NULL");
    }

    protected function alterFloat(string $table, string $column): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE DOUBLE PRECISION USING {$column}::DOUBLE PRECISION");
            return;
        }
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` FLOAT NULL");
    }
}
