<?php

namespace App\Services\SFE;

use App\Models\Entete;
use App\Models\SfeStatusSnapshot;

class StatusSnapshotService
{
    protected $client;

    public function __construct(BridgeClient $client)
    {
        $this->client = $client;
    }

    public function refresh(): array
    {
        try {
            $data = $this->client->getMany([
                'bridge' => '/api/v1/status',
                'device' => '/api/v1/mcf/etat',
                'config' => '/api/v1/config',
                'ports' => '/api/v1/ports',
                'connexion' => '/api/v1/mcf/connexion',
            ], 5);

            $settings = Entete::first();
            $bridgeOnline = !empty($data['bridge']) && empty($data['bridge']['erreur']);
            $deviceReady = (bool) data_get($data, 'device.succes', false);
            $dgiConnected = (bool) data_get($data, 'connexion.succes', false);
            $configurationValid = (bool) ($settings && $settings->ifu && $settings->denomination && $settings->sfe_bridge_url);
            $ready = $bridgeOnline && $deviceReady && $configurationValid;

            $snapshot = SfeStatusSnapshot::updateOrCreate(
                ['snapshot_key' => 'current'],
                [
                    'level' => !$bridgeOnline ? 'disconnected' : ($ready ? 'connected' : 'warning'),
                    'message' => !$bridgeOnline ? 'Bridge SFE indisponible.' : ($ready ? 'Facturation fiscale opérationnelle.' : 'Bridge joignable, mais boîtier ou configuration non prêt.'),
                    'bridge_online' => $bridgeOnline,
                    'device_ready' => $deviceReady,
                    'dgi_connected' => $dgiConnected,
                    'configuration_valid' => $configurationValid,
                    'bridge_data' => $data['bridge'] ?? [],
                    'device_data' => $data['device'] ?? [],
                    'connection_data' => $data['connexion'] ?? [],
                    'config_data' => $data['config'] ?? [],
                    'ports_data' => $data['ports'] ?? [],
                    'last_error' => null,
                    'checked_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            $snapshot = SfeStatusSnapshot::updateOrCreate(
                ['snapshot_key' => 'current'],
                [
                    'level' => 'disconnected',
                    'message' => 'Bridge SFE hors ligne ou délai dépassé.',
                    'bridge_online' => false,
                    'device_ready' => false,
                    'dgi_connected' => false,
                    'configuration_valid' => false,
                    'bridge_data' => [],
                    'device_data' => [],
                    'connection_data' => [],
                    'config_data' => [],
                    'ports_data' => [],
                    'last_error' => $e->getMessage(),
                    'checked_at' => now(),
                ]
            );
        }

        return $this->format($snapshot);
    }

    public function current(): array
    {
        $snapshot = SfeStatusSnapshot::where('snapshot_key', 'current')->first();

        return $snapshot ? $this->format($snapshot) : [
            'level' => 'warning',
            'message' => 'État SFE en attente de la première surveillance.',
            'bridge' => [], 'device' => [], 'connexion' => [], 'config' => [], 'ports' => [],
            'checked_at' => null,
        ];
    }

    protected function format(SfeStatusSnapshot $snapshot): array
    {
        return [
            'level' => $snapshot->level,
            'message' => $snapshot->message,
            'bridge' => $snapshot->bridge_data ?: [],
            'device' => $snapshot->device_data ?: [],
            'connexion' => $snapshot->connection_data ?: [],
            'config' => $snapshot->config_data ?: [],
            'ports' => $snapshot->ports_data ?: [],
            'bridge_online' => $snapshot->bridge_online,
            'device_ready' => $snapshot->device_ready,
            'dgi_connected' => $snapshot->dgi_connected,
            'configuration_valid' => $snapshot->configuration_valid,
            'erreur' => $snapshot->last_error,
            'checked_at' => optional($snapshot->checked_at)->toIso8601String(),
        ];
    }
}
