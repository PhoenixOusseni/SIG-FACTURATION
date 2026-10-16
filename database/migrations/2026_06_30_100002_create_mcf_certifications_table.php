<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMcfCertificationsTable extends Migration
{
    public function up()
    {
        Schema::create('mcf_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->onDelete('cascade');
            $table->foreignId('reference_certification_id')->nullable()
                ->constrained('mcf_certifications')->nullOnDelete();
            $table->string('type_facture');
            $table->string('nature_avoir')->nullable();
            $table->unsignedBigInteger('compteur_facture');
            $table->unsignedBigInteger('compteur_total');
            $table->string('nim');
            $table->string('ifu')->nullable();
            $table->string('sig');
            $table->text('qr_content');
            $table->timestamp('date_certification');
            $table->decimal('montant_ht', 15, 2)->default(0);
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('montant_ts', 15, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2)->default(0);
            $table->json('detail_taxation')->nullable();
            $table->string('statut')->default('certifiee');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mcf_certifications');
    }
}
