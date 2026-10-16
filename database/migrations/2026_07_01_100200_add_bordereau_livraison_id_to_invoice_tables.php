<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBordereauLivraisonIdToInvoiceTables extends Migration
{
    public function up()
    {
        Schema::table('facture_proformas', function (Blueprint $table) {
            if (!Schema::hasColumn('facture_proformas', 'bordereau_livraison_id')) {
                $table->foreignId('bordereau_livraison_id')->nullable()->after('statut')
                    ->constrained('bordereau_livraisons')->onUpdate('cascade')->onDelete('set null');
            }
        });

        Schema::table('factures', function (Blueprint $table) {
            if (!Schema::hasColumn('factures', 'bordereau_livraison_id')) {
                $table->foreignId('bordereau_livraison_id')->nullable()->after('facture_proforma_id')
                    ->constrained('bordereau_livraisons')->onUpdate('cascade')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('facture_proformas', function (Blueprint $table) {
            if (Schema::hasColumn('facture_proformas', 'bordereau_livraison_id')) {
                $table->dropForeign(['bordereau_livraison_id']);
                $table->dropColumn('bordereau_livraison_id');
            }
        });

        Schema::table('factures', function (Blueprint $table) {
            if (Schema::hasColumn('factures', 'bordereau_livraison_id')) {
                $table->dropForeign(['bordereau_livraison_id']);
                $table->dropColumn('bordereau_livraison_id');
            }
        });
    }
}
