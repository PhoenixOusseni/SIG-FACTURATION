<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElementTransfertsStockTable extends Migration
{
    public function up()
    {
        Schema::create('element_transferts_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfert_stock_id')->constrained('transferts_stock')->onDelete('cascade');
            $table->foreignId('produit_id')->constrained('produits');
            $table->decimal('quantite', 15, 3);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('element_transferts_stock');
    }
}
