<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxeSpecifiqueToProduitsTable extends Migration
{
    public function up()
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->decimal('montant_ts', 15, 2)->nullable();
            $table->string('description_ts', 500)->nullable()->after('montant_ts');
        });
    }

    public function down()
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['montant_ts', 'description_ts']);
        });
    }
}
