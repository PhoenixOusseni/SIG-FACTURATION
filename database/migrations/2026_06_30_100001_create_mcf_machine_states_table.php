<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMcfMachineStatesTable extends Migration
{
    public function up()
    {
        Schema::create('mcf_machine_states', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->unsignedInteger('acnt')->default(1);
            $table->string('ifu')->nullable();
            $table->unsignedBigInteger('compteur_total')->default(0);
            $table->unsignedBigInteger('compteur_vente')->default(0);
            $table->unsignedBigInteger('compteur_avoir')->default(0);
            $table->timestamp('derniere_facture_date')->nullable();
            $table->string('derniere_facture_type')->nullable();
            $table->string('derniere_facture_sig')->nullable();
            $table->string('derniere_facture_numero')->nullable();
            $table->decimal('dernier_montant', 15, 2)->nullable();
            $table->timestamp('derniere_connexion_serveur')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mcf_machine_states');
    }
}
