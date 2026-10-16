<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvoirFiscalAndCertLinks extends Migration
{
    public function up()
    {
        Schema::table('facture_avoirs', function (Blueprint $table) {
            if (!Schema::hasColumn('facture_avoirs', 'nature_avoir')) {
                $table->string('nature_avoir', 8)->nullable()->after('motif');
            }
            if (!Schema::hasColumn('facture_avoirs', 'sfe_locked_at')) {
                $table->timestamp('sfe_locked_at')->nullable()->after('statut');
            }
            if (!Schema::hasColumn('facture_avoirs', 'dgi_signature')) {
                $table->string('dgi_signature')->nullable();
            }
            if (!Schema::hasColumn('facture_avoirs', 'dgi_nim_machine')) {
                $table->string('dgi_nim_machine')->nullable();
            }
            if (!Schema::hasColumn('facture_avoirs', 'dgi_compteur')) {
                $table->string('dgi_compteur')->nullable();
            }
            if (!Schema::hasColumn('facture_avoirs', 'dgi_qrcode')) {
                $table->longText('dgi_qrcode')->nullable();
            }
            if (!Schema::hasColumn('facture_avoirs', 'modepaie_id')) {
                $table->foreignId('modepaie_id')->nullable()->constrained('modepaies')->nullOnDelete();
            }
        });

        Schema::table('mcf_certifications', function (Blueprint $table) {
            if (!Schema::hasColumn('mcf_certifications', 'facture_avoir_id')) {
                $table->foreignId('facture_avoir_id')->nullable()->after('facture_id')
                    ->constrained('facture_avoirs')->nullOnDelete();
            }
        });

        // Allow null facture_id for edge RRR avoirs without original (if DB supports change).
        // Keep existing FKs; facture_id remains set when original exists.
    }

    public function down()
    {
        Schema::table('mcf_certifications', function (Blueprint $table) {
            if (Schema::hasColumn('mcf_certifications', 'facture_avoir_id')) {
                $table->dropConstrainedForeignId('facture_avoir_id');
            }
        });

        Schema::table('facture_avoirs', function (Blueprint $table) {
            $cols = [
                'nature_avoir', 'sfe_locked_at', 'dgi_signature',
                'dgi_nim_machine', 'dgi_compteur', 'dgi_qrcode',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('facture_avoirs', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('facture_avoirs', 'modepaie_id')) {
                $table->dropConstrainedForeignId('modepaie_id');
            }
        });
    }
}
