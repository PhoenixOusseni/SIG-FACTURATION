<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReglementFournisseursTable extends Migration
{
    public function up()
    {
        Schema::create('reglement_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->date('date');
            $table->string('mode');
            $table->decimal('montant', 14, 2)->default(0);
            $table->string('reference_paiement')->nullable();
            $table->text('observation')->nullable();

            $table->foreignId('facture_fournisseur_id')->constrained('facture_fournisseurs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('users_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reglement_fournisseurs');
    }
}
