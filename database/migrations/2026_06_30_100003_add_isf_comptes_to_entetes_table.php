<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsfComptesToEntetesTable extends Migration
{
    public function up()
    {
        Schema::table('entetes', function (Blueprint $table) {
            $table->string('identifiant_sfe')->nullable()->after('division_fiscal');
            $table->string('comptes_bancaires')->nullable()->after('identifiant_sfe');
        });
    }

    public function down()
    {
        Schema::table('entetes', function (Blueprint $table) {
            $table->dropColumn(['identifiant_sfe', 'comptes_bancaires']);
        });
    }
}
