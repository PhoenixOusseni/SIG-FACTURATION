<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SfeStatusSnapshot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'bridge_online' => 'boolean',
        'device_ready' => 'boolean',
        'dgi_connected' => 'boolean',
        'configuration_valid' => 'boolean',
        'bridge_data' => 'array',
        'device_data' => 'array',
        'connection_data' => 'array',
        'config_data' => 'array',
        'ports_data' => 'array',
        'checked_at' => 'datetime',
    ];
}
