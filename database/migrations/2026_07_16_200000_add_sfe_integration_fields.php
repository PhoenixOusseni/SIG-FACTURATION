<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSfeIntegrationFields extends Migration
{
    public function up()
    {
        Schema::table('entetes', function (Blueprint $table) {
            $table->string('sfe_bridge_url')->default('http://127.0.0.1:8000');
            $table->unsignedInteger('sfe_bridge_timeout')->default(10);
            $table->boolean('sfe_automatic_health_check')->default(true);
            $table->boolean('sfe_automatic_invoice_signing')->default(false);
            $table->string('sfe_company_address')->nullable();
        });
        Schema::table('factures', function (Blueprint $table) {
            $table->timestamp('sfe_locked_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('factures', function (Blueprint $table) { $table->dropColumn('sfe_locked_at'); });
        Schema::table('entetes', function (Blueprint $table) {
            $table->dropColumn(['sfe_bridge_url', 'sfe_bridge_timeout', 'sfe_automatic_health_check',
                'sfe_automatic_invoice_signing', 'sfe_company_address']);
        });
    }
}
