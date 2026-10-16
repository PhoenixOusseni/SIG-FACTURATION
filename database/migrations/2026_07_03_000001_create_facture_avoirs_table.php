<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFactureAvoirsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facture_avoirs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->date('date')->nullable();
            $table->string('reference')->nullable();
            $table->string('statut')->default('en attente');
            $table->string('objet')->nullable();
            $table->decimal('remise_1', 15, 2)->nullable();
            $table->decimal('remise_2', 15, 2)->nullable();
            
            // Motif de l'avoir (retour, erreur, annulation, remise, produit_defectueux)
            $table->string('motif')->nullable();
            
            // Référence de la facture d'origine
            $table->foreignId('facture_id')->nullable()->constrained('factures')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('users_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('contribuables_id')->nullable()->constrained('contribuables')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('facture_avoirs');
    }
}
