<?php

namespace App\Services\SFE;

use App\Models\Entete;
use App\Models\SfeStatusSnapshot;

class StatusSnapshotService
{
    protected DeviceStatusService $device;
    protected ConfigurationService $configuration;

    public function __construct(DeviceStatusService $device, ConfigurationService $configuration)
    {
        $this->device = $device;
        $this->configuration = $configuration;
    }

    public function refresh(): array
    {
        try {
            $settings = Entete::singleton();
            $deviceState = $this->device->state();
            $connexion = $this->device->connection();
            $config = $this->configuration->get();
            $ports = $this->device->ports();

            $isHardware = !empty($deviceState['hardware']);
            $deviceReady = (bool) ($deviceState['succes'] ?? false);
            $dgiConnected = (bool) ($connexion['succes'] ?? false);
            $configurationValid = (bool) ($settings->ifu && $settings->denomination);

            $bridgeData = [
                'status' => 'Driver MCF Natif PHP opérationnel',
                'version' => 'Norme SECeF BF ST 1.0',
                'mode' => $isHardware ? 'Matériel physique Eltrade CC300' : 'Émulateur fiscal SECeF',
            ];

            if ($isHardware && $deviceReady) {
                $level = 'connected';
                $message = 'Boîtier physique Eltrade CC300 connecté et opérationnel sur ' . ($settings->port_com ?: 'COM') . ' (NIM: ' . ($deviceState['nim'] ?? '—') . ').';
            } elseif (strtoupper((string) $settings->port_com) === 'SIMULATION') {
                $level = 'warning';
                $message = 'Mode Émulateur SECeF actif. Facturation logicielle opérationnelle.';
            } elseif (!empty($settings->port_com)) {
                $level = 'disconnected';
                $message = 'Boîtier physique MCF Eltrade non détecté sur le port ' . $settings->port_com . '. Vérifiez le câble USB et l\'alimentation.';
            } elseif ($configurationValid) {
                $level = 'warning';
                $message = 'Aucun port COM configuré pour le boîtier physique.';
            } else {
                $level = 'disconnected';
                $message = 'Configuration fiscale incomplète. Renseignez l\'IFU et la dénomination de l\'entreprise.';
            }

            $snapshot = SfeStatusSnapshot::updateOrCreate(
                ['snapshot_key' => 'current'],
                [
                    'level'               => $level,
                    'message'             => $message,
                    'bridge_online'       => true,
                    'device_ready'        => $deviceReady,
                    'dgi_connected'       => $dgiConnected,
                    'configuration_valid' => $configurationValid,
                    'bridge_data'         => $bridgeData,
                    'device_data'         => $deviceState,
                    'connection_data'     => $connexion,
                    'config_data'         => $config,
                    'ports_data'          => $ports,
                    'last_error'          => null,
                    'checked_at'          => now(),
                ]
            );
        } catch (\Throwable $e) {
            $snapshot = SfeStatusSnapshot::updateOrCreate(
                ['snapshot_key' => 'current'],
                [
                    'level'               => 'disconnected',
                    'message'             => 'Erreur interrogation pilote MCF: ' . $e->getMessage(),
                    'bridge_online'       => true,
                    'device_ready'        => false,
                    'dgi_connected'       => false,
                    'configuration_valid' => false,
                    'bridge_data'         => ['status' => 'Driver MCF Natif actif'],
                    'device_data'         => [],
                    'connection_data'     => [],
                    'config_data'         => [],
                    'ports_data'          => [],
                    'last_error'          => $e->getMessage(),
                    'checked_at'          => now(),
                ]
            );
        }

        return $this->format($snapshot);
    }

    public function current(): array
    {
        $snapshot = SfeStatusSnapshot::where('snapshot_key', 'current')->first();

        // Si le snapshot n'existe pas ou date de plus de 30 secondes, rafraîchir en temps réel
        if (!$snapshot || !$snapshot->checked_at || $snapshot->checked_at->diffInSeconds(now()) > 30) {
            return $this->refresh();
        }

        return $this->format($snapshot);
    }

    protected function format(SfeStatusSnapshot $snapshot): array
    {
        return [
            'level'               => $snapshot->level,
            'message'             => $snapshot->message,
            'bridge'              => $snapshot->bridge_data ?: [],
            'device'              => $snapshot->device_data ?: [],
            'connexion'           => $snapshot->connection_data ?: [],
            'config'              => $snapshot->config_data ?: [],
            'ports'               => $snapshot->ports_data ?: [],
            'bridge_online'       => $snapshot->bridge_online,
            'device_ready'        => $snapshot->device_ready,
            'dgi_connected'       => $snapshot->dgi_connected,
            'configuration_valid' => $snapshot->configuration_valid,
            'erreur'              => $snapshot->last_error,
            'checked_at'          => optional($snapshot->checked_at)->toIso8601String(),
        ];
    }
}
