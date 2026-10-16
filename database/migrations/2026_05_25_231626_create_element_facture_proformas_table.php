<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElementFactureProformasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('element_facture_proformas', function (Blueprint $table) {
            $table->id();
            $table->string('designation')->nullable();
            $table->float('quantite')->nullable();
            $table->decimal('prix_unitaire', 15, 2)->nullable();
            $table->decimal('montant_total', 15, 2)->nullable();

            $table->foreignId('produits_id')->nullable()->constrained('produits')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('facture_proforma_id')->nullable()->constrained('facture_proformas')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('element_facture_proformas');
    }
}
