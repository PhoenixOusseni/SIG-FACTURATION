<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReglementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reglements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();                      // REG-00001/2026
            $table->date('date');                                    // date du règlement
            $table->string('mode');                                  // Espèces / Chèque / Virement / Mobile Money
            $table->decimal('montant', 14, 2)->default(0);
            $table->string('reference_paiement')->nullable();        // N° chèque, transaction, etc.
            $table->text('observation')->nullable();

            $table->foreignId('facture_id')->constrained('factures')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('users_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');

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
        Schema::dropIfExists('reglements');
    }
}
