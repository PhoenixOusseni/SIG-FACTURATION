<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElementBordereauLivraisonsTable extends Migration
{
    public function up()
    {
        Schema::create('element_bordereau_livraisons', function (Blueprint $table) {
            $table->id();
            $table->string('designation')->nullable();
            $table->float('quantite')->nullable();
            $table->decimal('prix_unitaire', 15, 2)->nullable();
            $table->decimal('montant_total', 15, 2)->nullable();
            $table->float('remise')->nullable();

            $table->foreignId('produits_id')->nullable()
                ->constrained('produits')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('bordereau_livraison_id')->nullable()
                ->constrained('bordereau_livraisons')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('element_bordereau_livraisons');
    }
}
