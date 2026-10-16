<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDgiFieldsToFacturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            // Champs de sécurité DGI (retour de l'API)
            $table->string('dgi_signature')->nullable();
            $table->string('dgi_nim_machine')->nullable();
            $table->string('dgi_compteur')->nullable();
            $table->longText('dgi_qrcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['dgi_signature', 'dgi_nim_machine', 'dgi_compteur', 'dgi_qrcode']);
        });
    }
}
