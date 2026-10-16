<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeClientAndPsvb extends Migration
{
    public function up()
    {
        Schema::table('contribuables', function (Blueprint $table) {
            if (!Schema::hasColumn('contribuables', 'type_client')) {
                // DGI / bridge: CC, PM, PP, PC (FEC 1.11 / BridgeFactureRequest.client)
                $table->string('type_client', 8)->default('CC')->after('categories_id');
            }
            if (!Schema::hasColumn('contribuables', 'email')) {
                $table->string('email')->nullable()->after('telephone');
            }
        });

        Schema::table('mcf_certifications', function (Blueprint $table) {
            if (!Schema::hasColumn('mcf_certifications', 'psvb')) {
                $table->decimal('psvb', 15, 4)->default(0)->after('montant_ttc');
            }
        });
    }

    public function down()
    {
        Schema::table('mcf_certifications', function (Blueprint $table) {
            if (Schema::hasColumn('mcf_certifications', 'psvb')) {
                $table->dropColumn('psvb');
            }
        });

        Schema::table('contribuables', function (Blueprint $table) {
            if (Schema::hasColumn('contribuables', 'type_client')) {
                $table->dropColumn('type_client');
            }
            if (Schema::hasColumn('contribuables', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
}
