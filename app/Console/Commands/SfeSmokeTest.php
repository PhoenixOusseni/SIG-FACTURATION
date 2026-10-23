<?php

namespace App\Console\Commands;

use App\Services\Mcf\McfSerialDriver;
use App\Services\SFE\ConfigurationService;
use App\Services\SFE\DeviceStatusService;
use App\Services\SFE\HealthCheckService;
use Illuminate\Console\Command;

/**
 * Smoke test du driver série natif MCF Eltrade (Norme SECeF BF ST 1.0).
 */
class SfeSmokeTest extends Command
{
    protected $signature = 'sfe:smoke
                            {--payload : Dump a sample certifier payload shape}
                            {--json : Machine-readable JSON output}';

    protected $description = 'Smoke-test du pilote MCF natif : status, config, mcf/etat, mcf/connexion, ports COM';

    public function handle(
        HealthCheckService $health,
        DeviceStatusService $device,
        ConfigurationService $configuration,
        McfSerialDriver $driver
    ): int {
        $results = [
            'pilote' => 'Driver natif PHP Eltrade CC300 (SECeF ST 1.0)',
            'checks' => [],
        ];

        $ok = true;

        // 1) Aggregated health (status + etat)
        try {
            $status = $health->check();
            $results['checks']['health'] = [
                'ok' => in_array($status['level'] ?? '', ['connected', 'warning'], true),
                'level' => $status['level'] ?? null,
                'message' => $status['message'] ?? null,
            ];
            if (($status['level'] ?? '') === 'disconnected') {
                $ok = false;
            }
        } catch (\Throwable $e) {
            $ok = false;
            $results['checks']['health'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // 2) Configuration
        $results['checks']['config'] = $this->tryCall(function () use ($configuration) {
            return $configuration->get();
        }, $ok);

        // 3) État machine MCF (C1h)
        $results['checks']['mcf_etat'] = $this->tryCall(function () use ($device) {
            return $device->state();
        }, $ok);

        // 4) Connexion DGI (C2h)
        $results['checks']['mcf_connexion'] = $this->tryCall(function () use ($device) {
            return $device->connection();
        }, $ok);

        // 5) Ports COM disponibles
        $results['checks']['ports_com'] = $this->tryCall(function () use ($device) {
            return $device->ports();
        }, $ok);

        if ($this->option('payload')) {
            $results['sample_certifier_payload'] = [
                'numero_interne' => 'FACT-SMOKE-001',
                'type_facture' => 'FV',
                'mode_prix' => 'TTC',
                'categorie_psvb' => 'A',
                'nature_avoir' => '',
                'reference_originale' => '',
                'client' => [
                    'type_client' => 'PM',
                    'nom' => 'SOCIETE TEST',
                    'telephone' => '25334455',
                    'adresse' => 'Ouaga',
                    'ifu' => '00001234A',
                    'rccm' => '',
                    'email' => null,
                ],
                'lignes' => [[
                    'designation' => 'Article test',
                    'quantite' => 1,
                    'prix_unitaire' => 1180.0,
                    'type_article_dgi' => 'LOCBIE',
                    'groupe_taxe' => 'B',
                    'taux_tva' => 18.0,
                    'montant_ts' => 0,
                ]],
                'paiements' => [['mode' => 'ESP', 'montant' => 1180.0]],
            ];
            $results['sample_avoir_keys'] = [
                'type_facture' => 'FA',
                'nature_avoir' => 'RAN',
                'reference_originale' => 'EL02000018-1-374-1255',
            ];
        }

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->info('Smoke-test Pilote MCF Natif — ' . $results['pilote']);
            foreach ($results['checks'] as $name => $check) {
                $pass = $check['ok'] ?? false;
                $this->line(sprintf(
                    '  [%s] %s %s',
                    $pass ? 'PASS' : 'FAIL',
                    str_pad($name, 16),
                    $check['error'] ?? ''
                ));
            }
        }

        return $ok ? 0 : 1;
    }

    protected function tryCall(callable $fn, bool &$globalOk): array
    {
        try {
            $data = $fn();
            return ['ok' => true, 'data' => $data];
        } catch (\Throwable $e) {
            $globalOk = false;
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
