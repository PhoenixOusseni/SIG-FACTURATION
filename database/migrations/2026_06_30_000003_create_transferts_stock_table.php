<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfertsStockTable extends Migration
{
    public function up()
    {
        Schema::create('transferts_stock', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('service_source_id')->constrained('services');
            $table->foreignId('service_destination_id')->constrained('services');
            $table->enum('statut', ['en_attente', 'valide', 'annule'])->default('en_attente');
            $table->date('date')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transferts_stock');
    }
}
