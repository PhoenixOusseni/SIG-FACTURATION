<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElementFactureAvoirsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('element_facture_avoirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_avoir_id')->constrained('facture_avoirs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('produits_id')->nullable()->constrained('produits')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('quantite')->nullable();
            $table->decimal('prix_unitaire', 15, 2)->nullable();
            $table->decimal('montant_total', 15, 2)->nullable();
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
        Schema::dropIfExists('element_facture_avoirs');
    }
}
