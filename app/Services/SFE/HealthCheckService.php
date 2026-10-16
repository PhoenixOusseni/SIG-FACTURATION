<?php

namespace App\Services\SFE;

use App\Models\Entete;

class HealthCheckService
{
    protected $client;
    protected $device;

    public function __construct(BridgeClient $client, DeviceStatusService $device)
    {
        $this->client = $client;
        $this->device = $device;
    }

    public function check(): array
    {
        try {
            $bridge = $this->client->get('/api/v1/status');
            $device = $this->device->state();
            $settings = Entete::first();
            $valid = $settings && $settings->ifu && $settings->denomination && $settings->sfe_bridge_url;
            $ready = $valid && ($device['succes'] ?? false);
            return ['level' => $ready ? 'connected' : 'warning', 'bridge' => $bridge, 'device' => $device,
                'configuration_valid' => (bool) $valid,
                'message' => $ready ? 'Facturation fiscale opérationnelle.' : 'Bridge joignable, mais boîtier ou configuration non prêt.'];
        } catch (\Throwable $e) {
            return ['level' => 'disconnected', 'configuration_valid' => false, 'message' => $e->getMessage()];
        }
    }
}
