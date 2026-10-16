<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProduitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('designation')->nullable();
            $table->decimal('prix_unitaire', 15, 2)->nullable();
            $table->string('unite')->nullable();
            $table->float('qte')->nullable();
            $table->string('description')->nullable();
            $table->string('famille_produit')->nullable();

            $table->foreignId('tva_id')->nullable()->constrained('tvas')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('produits');
    }
}
