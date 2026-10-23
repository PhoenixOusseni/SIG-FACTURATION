<?php

namespace App\Services\SFE;

use App\Models\Entete;

class HealthCheckService
{
    protected DeviceStatusService $device;

    public function __construct(DeviceStatusService $device)
    {
        $this->device = $device;
    }

    public function check(): array
    {
        try {
            $device = $this->device->state();
            $settings = Entete::singleton();
            $valid = $settings && $settings->ifu && $settings->denomination;
            $ready = $valid && ($device['succes'] ?? false);

            return [
                'level'               => $ready ? 'connected' : 'warning',
                'bridge'              => ['status' => 'Driver MCF Natif PHP opérationnel', 'version' => '1.0'],
                'device'              => $device,
                'configuration_valid' => (bool) $valid,
                'message'             => $ready
                    ? 'Facturation fiscale opérationnelle.'
                    : 'Configuration ou état du boîtier incomplet.',
            ];
        } catch (\Throwable $e) {
            return [
                'level'               => 'warning',
                'configuration_valid' => false,
                'message'             => $e->getMessage(),
            ];
        }
    }
}
