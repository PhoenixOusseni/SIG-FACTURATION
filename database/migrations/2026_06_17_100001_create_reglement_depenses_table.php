<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglement_depenses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->date('date');
            $table->string('mode', 50);
            $table->decimal('montant', 14, 2)->default(0);
            $table->string('reference_paiement', 150)->nullable();
            $table->text('observation')->nullable();
            $table->foreignId('depense_id')->constrained('depenses')->cascadeOnDelete();
            $table->foreignId('users_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglement_depenses');
    }
};
