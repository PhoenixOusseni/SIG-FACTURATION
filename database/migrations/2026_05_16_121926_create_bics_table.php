<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bics', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // Code de la BIC, par exemple "BIC123" pour un BIC spécifique
            $table->string('intitule')->nullable();
            $table->float('taux')->nullable(); // Taux de BIC en pourcentage
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
        Schema::dropIfExists('bics');
    }
}
