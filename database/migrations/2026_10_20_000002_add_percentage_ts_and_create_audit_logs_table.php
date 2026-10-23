<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Mise à jour de la table produits pour supporter la Taxe Spécifique en Pourcentage
        Schema::table('produits', function (Blueprint $table) {
            if (!Schema::hasColumn('produits', 'type_ts')) {
                $table->string('type_ts', 16)->default('MONTANT')->after('montant_ts'); // 'MONTANT' ou 'POURCENTAGE'
            }
            if (!Schema::hasColumn('produits', 'pourcentage_ts')) {
                $table->decimal('pourcentage_ts', 8, 2)->nullable()->default(0)->after('type_ts');
            }
        });

        // 2. Création de la table audit_logs (Piste d'audit légale DGI)
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('nom_utilisateur', 100)->default('Système');
                $table->string('action', 50); // CREATE, UPDATE, DELETE, FISCALISATION, CLOTURE_Z, RAPPORT_X, RAPPORT_A, STOCK, CONNEXION
                $table->string('entite', 50); // FACTURE, AVOIR, ARTICLE, CLIENT, PARAMETRES, MCF, STOCK, CAISSE
                $table->string('entite_id', 50)->nullable();
                $table->text('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['entite', 'action']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            if (Schema::hasColumn('produits', 'pourcentage_ts')) {
                $table->dropColumn('pourcentage_ts');
            }
            if (Schema::hasColumn('produits', 'type_ts')) {
                $table->dropColumn('type_ts');
            }
        });

        Schema::dropIfExists('audit_logs');
    }
};
