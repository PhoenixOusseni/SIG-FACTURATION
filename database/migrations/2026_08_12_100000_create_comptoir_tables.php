<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateComptoirTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caisses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->foreignId('service_id')->constrained('services')->onUpdate('cascade')->onDelete('restrict');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('session_caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caisse_id')->constrained('caisses')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('service_id')->constrained('services')->onUpdate('cascade')->onDelete('restrict');
            $table->date('date_metier');
            $table->string('statut')->default('ouverte'); // ouverte | en_arret | fermee
            $table->decimal('fond_initial', 15, 2)->default(0);
            $table->unsignedInteger('dernier_numero')->default(0);
            $table->foreignId('opened_by')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->timestamp('opened_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->timestamp('closed_at')->nullable();

            // Fermeture snapshot (immutable once set)
            $table->decimal('fermeture_montant_ventes_ttc', 15, 2)->nullable();
            $table->unsignedInteger('fermeture_nombre_tickets')->nullable();
            $table->decimal('fermeture_montant_annulations', 15, 2)->nullable();
            $table->json('fermeture_detail_modes')->nullable();
            $table->json('fermeture_detail_articles')->nullable();
            $table->decimal('fermeture_fond_theorique', 15, 2)->nullable();
            $table->decimal('montant_fermeture_declare', 15, 2)->nullable();
            $table->decimal('fermeture_ecart', 15, 2)->nullable();
            $table->text('notes_ouverture')->nullable();
            $table->text('notes_fermeture')->nullable();

            $table->timestamps();

            $table->index(['caisse_id', 'date_metier']);
            $table->index(['caisse_id', 'statut']);
        });

        // At most one non-closed session per caisse (PostgreSQL partial unique).
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                "CREATE UNIQUE INDEX session_caisses_one_open_per_caisse
                 ON session_caisses (caisse_id)
                 WHERE statut IN ('ouverte', 'en_arret')"
            );
        }

        Schema::create('arret_caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_caisse_id')->constrained('session_caisses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->timestamp('arrete_at');
            $table->decimal('montant_ventes_ttc', 15, 2)->default(0);
            $table->unsignedInteger('nombre_tickets')->default(0);
            $table->decimal('montant_annulations', 15, 2)->default(0);
            $table->json('detail_modes')->nullable();
            $table->json('detail_articles')->nullable();
            $table->decimal('fond_theorique', 15, 2)->default(0);
            $table->decimal('fond_declare', 15, 2)->nullable();
            $table->decimal('ecart', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_comptoirs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedInteger('numero');
            $table->foreignId('session_caisse_id')->constrained('session_caisses')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('caisse_id')->constrained('caisses')->onUpdate('cascade')->onDelete('restrict');
            $table->date('date_metier');
            $table->foreignId('contribuable_id')->nullable()->constrained('contribuables')->onUpdate('cascade')->onDelete('set null');
            $table->string('client_libre')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->string('statut')->default('brouillon'); // brouillon | en_attente | paye | annule | converti
            $table->decimal('remise', 15, 2)->default(0);
            $table->decimal('montant_ht', 15, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2)->default(0);
            $table->timestamp('stock_moved_at')->nullable();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->onUpdate('cascade')->onDelete('set null');
            $table->timestamp('annule_at')->nullable();
            $table->foreignId('annule_par')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->string('motif_annulation')->nullable();
            $table->timestamps();

            $table->unique(['caisse_id', 'date_metier', 'numero'], 'ticket_comptoirs_caisse_date_numero_unique');
            $table->index(['session_caisse_id', 'statut']);
            $table->index(['date_metier', 'statut']);
            $table->index('stock_moved_at');
        });

        Schema::create('element_ticket_comptoirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_comptoir_id')->constrained('ticket_comptoirs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('produits_id')->constrained('produits')->onUpdate('cascade')->onDelete('restrict');
            $table->string('designation')->nullable();
            $table->decimal('quantite', 15, 3)->default(0);
            $table->decimal('prix_unitaire', 15, 2)->default(0); // HT
            $table->decimal('tva_taux', 8, 2)->default(0);
            $table->decimal('montant_ht', 15, 2)->default(0);
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('montant_ttc_ligne', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('reglement_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->foreignId('ticket_comptoir_id')->constrained('ticket_comptoirs')->onUpdate('cascade')->onDelete('cascade');
            $table->date('date');
            $table->foreignId('modepaie_id')->constrained('modepaies')->onUpdate('cascade')->onDelete('restrict');
            $table->string('mode')->nullable();
            $table->decimal('montant', 14, 2)->default(0);
            $table->decimal('montant_recu', 14, 2)->nullable();
            $table->decimal('rendu', 14, 2)->nullable();
            $table->string('reference_paiement')->nullable();
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
        Schema::dropIfExists('reglement_tickets');
        Schema::dropIfExists('element_ticket_comptoirs');
        Schema::dropIfExists('ticket_comptoirs');
        Schema::dropIfExists('arret_caisses');

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS session_caisses_one_open_per_caisse');
        }

        Schema::dropIfExists('session_caisses');
        Schema::dropIfExists('caisses');
    }
}
