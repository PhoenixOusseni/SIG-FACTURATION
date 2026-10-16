<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContribuablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contribuables', function (Blueprint $table) {
            $table->id();
            $table->string('assujeti')->nullable();
            $table->string('adresse')->nullable();
            $table->string('adresse_localisation')->nullable();
            $table->string('telephone')->nullable();
            $table->string('ifu')->nullable();
            $table->string('rccm')->nullable();
            $table->string('division_fiscal')->nullable();
            $table->string('regime_fiscal')->nullable();
            $table->string('adresse_cadas')->nullable();

            $table->foreignId('categories_id')->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('contribuables');
    }
}
