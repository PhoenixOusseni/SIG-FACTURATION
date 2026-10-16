<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->date('date')->nullable();
            $table->string('reference')->nullable();
            $table->string('statut')->default('Brouillon');
            $table->decimal('remise_1', 15, 2)->nullable();
            $table->decimal('remise_2', 15, 2)->nullable();

            $table->foreignId('facture_proforma_id')->nullable()->constrained('facture_proformas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('contribuable_id')->nullable()->constrained('contribuables')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
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
        Schema::dropIfExists('factures');
    }
}
