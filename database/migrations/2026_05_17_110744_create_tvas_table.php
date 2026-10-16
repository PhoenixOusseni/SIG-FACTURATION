<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTvasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tvas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // Code de la TVA, par exemple "TVA20" pour 20%
            $table->string('intitule')->nullable();
            $table->integer('compte_tva')->nullable();
            $table->float('taux')->nullable(); // Taux de TVA en pourcentage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tvas');
    }
}
