<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les colonnes de remise par ligne (type, valeur, montant)
     * et fiabilise les quantités décimales sur toutes les tables d'éléments.
     */
    public function up(): void
    {
        $tables = ['element_factures', 'element_facture_proformas', 'element_facture_avoirs'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'remise_type')) {
                        $table->string('remise_type', 20)->default('POURCENTAGE')->after('prix_unitaire');
                    }
                    if (!Schema::hasColumn($tableName, 'remise_valeur')) {
                        $table->decimal('remise_valeur', 15, 2)->default(0)->after('remise_type');
                    }
                    if (!Schema::hasColumn($tableName, 'remise_montant')) {
                        $table->decimal('remise_montant', 15, 2)->default(0)->after('remise_valeur');
                    }
                });
            }
        }

        // Fiabiliser quantite sur element_facture_avoirs si MySQL/MariaDB
        if (Schema::hasTable('element_facture_avoirs') && Schema::hasColumn('element_facture_avoirs', 'quantite')) {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `element_facture_avoirs` MODIFY COLUMN `quantite` FLOAT NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['element_factures', 'element_facture_proformas', 'element_facture_avoirs'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $cols = [];
                    if (Schema::hasColumn($tableName, 'remise_montant')) $cols[] = 'remise_montant';
                    if (Schema::hasColumn($tableName, 'remise_valeur')) $cols[] = 'remise_valeur';
                    if (Schema::hasColumn($tableName, 'remise_type')) $cols[] = 'remise_type';
                    if (!empty($cols)) {
                        $table->dropColumn($cols);
                    }
                });
            }
        }
    }
};
