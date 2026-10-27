<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMouvementsCaisseTable extends Migration
{
    public function up()
    {
        Schema::create('mouvements_caisse', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // DEP-00001/2026 ou RET-00001/2026
            $table->enum('type', ['DEPOT', 'RETRAIT']); // DEPOT (entrée) ou RETRAIT (sortie)
            $table->decimal('montant', 15, 2);
            $table->dateTime('date_mouvement');
            $table->string('motif');
            $table->string('type_motif')->default('AUTRE'); // APPORT_MONNAIE, RENFLOUEMENT, APPORT_EXPLOITANT, VERSEMENT_BANQUE, ACHAT_URGENT, DEPENSE_FONCTIONNEMENT, AUTRE
            $table->string('beneficiaire')->nullable();
            $table->string('piece_justificative')->nullable();
            $table->text('observation')->nullable();

            $table->foreignId('caisse_id')->nullable()->constrained('caisses')->nullOnDelete();
            $table->foreignId('session_caisse_id')->nullable()->constrained('session_caisses')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mouvements_caisse');
    }
}
