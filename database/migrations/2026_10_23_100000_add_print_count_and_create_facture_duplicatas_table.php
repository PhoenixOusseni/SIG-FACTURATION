<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrintCountAndCreateFactureDuplicatasTable extends Migration
{
    public function up()
    {
        // 1. Colonnes de suivi d'impression pour Duplicata
        if (Schema::hasTable('factures') && !Schema::hasColumn('factures', 'print_count')) {
            Schema::table('factures', function (Blueprint $table) {
                $table->unsignedInteger('print_count')->default(0)->after('statut');
                $table->timestamp('first_printed_at')->nullable()->after('print_count');
            });
        }

        if (Schema::hasTable('facture_avoirs') && !Schema::hasColumn('facture_avoirs', 'print_count')) {
            Schema::table('facture_avoirs', function (Blueprint $table) {
                $table->unsignedInteger('print_count')->default(0)->after('statut');
                $table->timestamp('first_printed_at')->nullable()->after('print_count');
            });
        }

        // 2. Table d'archives immuables de duplicatas (Piste d'Audit Fiable DGI)
        if (!Schema::hasTable('facture_duplicatas')) {
            Schema::create('facture_duplicatas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
                $table->string('numero_interne', 50)->index();
                $table->timestamp('date_certification')->nullable();
                $table->longText('vendeur_json');
                $table->longText('client_json');
                $table->longText('lignes_json');
                $table->longText('paiements_json')->nullable();
                $table->longText('totaux_json');
                $table->longText('dgi_json');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('facture_duplicatas');

        if (Schema::hasTable('factures') && Schema::hasColumn('factures', 'print_count')) {
            Schema::table('factures', function (Blueprint $table) {
                $table->dropColumn(['print_count', 'first_printed_at']);
            });
        }

        if (Schema::hasTable('facture_avoirs') && Schema::hasColumn('facture_avoirs', 'print_count')) {
            Schema::table('facture_avoirs', function (Blueprint $table) {
                $table->dropColumn(['print_count', 'first_printed_at']);
            });
        }
    }
}
