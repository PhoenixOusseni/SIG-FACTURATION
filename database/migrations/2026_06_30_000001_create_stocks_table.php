<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->decimal('quantite', 15, 3)->default(0);
            $table->timestamps();
            $table->unique(['produit_id', 'service_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}
