<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBordereauLivraisonsTable extends Migration
{
    public function up()
    {
        Schema::create('bordereau_livraisons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();        // BL-00001/2026
            $table->date('date')->nullable();
            $table->string('reference')->nullable();

            // en attente | livré | facturé | annulé
            $table->string('statut')->default('en attente');
            $table->string('transporteur')->nullable();
            $table->string('livreur')->nullable();
            $table->text('observation')->nullable();
            $table->string('objet')->nullable();
            $table->decimal('remise', 15, 2)->nullable();

            // Origine éventuelle (peut être null = BL créé directement)
            $table->foreignId('facture_proforma_id')->nullable()
                ->constrained('facture_proformas')->onUpdate('cascade')->onDelete('set null');

            // Quand le BL est transformé en facture
            $table->foreignId('facture_id')->nullable()
                ->constrained('factures')->onUpdate('cascade')->onDelete('set null');

            $table->foreignId('users_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('contribuables_id')->nullable()->constrained('contribuables')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bordereau_livraisons');
    }
}
