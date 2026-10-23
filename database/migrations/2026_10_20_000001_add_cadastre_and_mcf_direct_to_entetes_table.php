<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCadastreAndMcfDirectToEntetesTable extends Migration
{
    public function up()
    {
        Schema::table('entetes', function (Blueprint $table) {
            if (!Schema::hasColumn('entetes', 'infos_cadastrales')) {
                $table->string('infos_cadastrales', 150)->nullable()->after('postale');
            }
            if (!Schema::hasColumn('entetes', 'port_com')) {
                $table->string('port_com', 30)->nullable()->after('identifiant_sfe');
            }
            if (!Schema::hasColumn('entetes', 'mcf_baudrate')) {
                $table->integer('mcf_baudrate')->default(9600)->after('port_com');
            }
            if (!Schema::hasColumn('entetes', 'mcf_parite')) {
                $table->string('mcf_parite', 10)->default('NONE')->after('mcf_baudrate');
            }
            if (!Schema::hasColumn('entetes', 'nim_mcf')) {
                $table->string('nim_mcf', 50)->nullable()->after('mcf_parite');
            }
            if (!Schema::hasColumn('entetes', 'categorie_psvb')) {
                $table->string('categorie_psvb', 5)->default('A')->nullable()->after('nim_mcf');
            }
        });
    }

    public function down()
    {
        Schema::table('entetes', function (Blueprint $table) {
            $table->dropColumn([
                'infos_cadastrales',
                'port_com',
                'mcf_baudrate',
                'mcf_parite',
                'nim_mcf',
                'categorie_psvb'
            ]);
        });
    }
}
