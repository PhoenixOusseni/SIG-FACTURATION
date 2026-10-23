<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mcf_rapports', function (Blueprint $table) {
            $table->id();
            $table->string('type_rapport', 16); // 'X', 'Z', 'PERIODIQUE'
            $table->string('numero_rapport', 32)->nullable(); // ex: 'Z000001', 'X20261020-001'
            $table->dateTime('date_rapport');
            $table->dateTime('date_debut')->nullable();
            $table->dateTime('date_fin')->nullable();

            $table->decimal('chiffre_affaires_ht', 15, 2)->default(0);
            $table->decimal('total_tva', 15, 2)->default(0);
            $table->decimal('total_ts', 15, 2)->default(0);
            $table->decimal('total_psvb', 15, 2)->default(0);
            $table->decimal('total_timbre', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);
            $table->decimal('reductions_commerciales', 15, 2)->default(0);

            $table->integer('nb_factures_vente')->default(0);
            $table->integer('nb_factures_avoir')->default(0);
            $table->integer('ventes_incompletes')->default(0);

            $table->json('details_taxes')->nullable(); // Agrégation par groupe (A, B, C...)
            $table->json('details_reglements')->nullable(); // Modes de règlement (Espèces, Virement...)
            $table->json('details_bruts')->nullable(); // Agrégats complets

            $table->string('statut', 32)->default('ENREGISTRE'); // 'ENREGISTRE', 'CLOTURE'
            $table->text('raw_mcf_response')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcf_rapports');
    }
};
