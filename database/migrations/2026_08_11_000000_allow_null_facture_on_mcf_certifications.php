<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AllowNullFactureOnMcfCertifications extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('mcf_certifications') || DB::getDriverName() === 'sqlite') {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE mcf_certifications MODIFY facture_id BIGINT UNSIGNED NULL');
        }
    }

    public function down()
    {
        // Existing RRR certifications may legitimately have no source invoice,
        // so reverting to NOT NULL would be destructive.
    }
}
