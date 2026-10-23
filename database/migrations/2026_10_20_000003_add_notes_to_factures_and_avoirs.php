<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('factures') && !Schema::hasColumn('factures', 'notes')) {
            Schema::table('factures', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('bic_id');
            });
        }

        if (Schema::hasTable('facture_avoirs') && !Schema::hasColumn('facture_avoirs', 'notes')) {
            Schema::table('facture_avoirs', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('motif');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('factures') && Schema::hasColumn('factures', 'notes')) {
            Schema::table('factures', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }

        if (Schema::hasTable('facture_avoirs') && Schema::hasColumn('facture_avoirs', 'notes')) {
            Schema::table('facture_avoirs', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
