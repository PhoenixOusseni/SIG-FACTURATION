<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnlargeMonetaryColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrandit les colonnes monétaires (`prix_unitaire`, `montant_total`) sur
     * les tables d'éléments et la table `produits` pour accepter de gros
     * montants. Les colonnes passent de `FLOAT` (double(8,2)) à `DECIMAL(15,2)`.
     *
     * @return void
     */
    public function up()
    {
        // SQLite does not support portable ALTER TYPE; dynamic typing is enough for tests.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $targets = [
            'element_facture_proformas'    => ['prix_unitaire', 'montant_total'],
            'element_factures'             => ['prix_unitaire', 'montant_total'],
            'element_bordereau_livraisons' => ['prix_unitaire', 'montant_total'],
            'produits'                     => ['prix_unitaire'],
        ];

        foreach ($targets as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $this->alterDecimal($table, $column);
                }
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

        $targets = [
            'element_facture_proformas'    => ['prix_unitaire', 'montant_total'],
            'element_factures'             => ['prix_unitaire', 'montant_total'],
            'element_bordereau_livraisons' => ['prix_unitaire', 'montant_total'],
            'produits'                     => ['prix_unitaire'],
        ];

        foreach ($targets as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $this->alterFloat($table, $column);
                }
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
        // MySQL / MariaDB
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
