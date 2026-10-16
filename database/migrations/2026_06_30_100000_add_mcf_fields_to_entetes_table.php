<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMcfFieldsToEntetesTable extends Migration
{
    public function up()
    {
        Schema::table('entetes', function (Blueprint $table) {
            $table->string('ifu')->nullable()->after('denomination');
            $table->string('rccm')->nullable()->after('ifu');
            $table->string('regime_fiscal')->nullable()->after('rccm');
            $table->string('division_fiscal')->nullable()->after('regime_fiscal');
        });
    }

    public function down()
    {
        Schema::table('entetes', function (Blueprint $table) {
            $table->dropColumn(['ifu', 'rccm', 'regime_fiscal', 'division_fiscal']);
        });
    }
}
