<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSfeStatusSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('sfe_status_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('snapshot_key')->unique()->default('current');
            $table->string('level')->default('warning');
            $table->string('message')->nullable();
            $table->boolean('bridge_online')->default(false);
            $table->boolean('device_ready')->default(false);
            $table->boolean('dgi_connected')->default(false);
            $table->boolean('configuration_valid')->default(false);
            $table->json('bridge_data')->nullable();
            $table->json('device_data')->nullable();
            $table->json('connection_data')->nullable();
            $table->json('config_data')->nullable();
            $table->json('ports_data')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('checked_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sfe_status_snapshots');
    }
}
