<?php

namespace App\Console\Commands;

use App\Services\SFE\BridgeClient;
use App\Services\SFE\ConfigurationService;
use App\Services\SFE\DeviceStatusService;
use App\Services\SFE\HealthCheckService;
use Illuminate\Console\Command;

/**
 * Live smoke against the SFE Bridge REST API (manuel déploiement).
 * Does not certify invoices — only admin endpoints + payload shape dump.
 */
class SfeSmokeTest extends Command
{
    protected $signature = 'sfe:smoke
                            {--payload : Dump a sample certifier payload shape (no HTTP cert call)}
                            {--json : Machine-readable JSON output}';

    protected $description = 'Smoke-test SFE Bridge: status, config, mcf/etat, mcf/connexion';

    public function handle(
        HealthCheckService $health,
        DeviceStatusService $device,
        ConfigurationService $configuration,
        BridgeClient $client
    ): int {
        $results = [
            'bridge_url' => rtrim((string) (optional(\App\Models\Entete::first())->sfe_bridge_url
                ?? config('sfe.url')), '/'),
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

        // 2) GET /api/v1/status (raw)
        $results['checks']['status'] = $this->tryCall(function () use ($client) {
            return $client->get('/api/v1/status');
        }, $ok);

        // 3) GET /api/v1/config
        $results['checks']['config'] = $this->tryCall(function () use ($configuration) {
            $cfg = $configuration->get();
            // Never echo license_key in full
            if (isset($cfg['license_key']) && $cfg['license_key'] !== '') {
                $cfg['license_key'] = '***';
            }
            return $cfg;
        }, $ok);

        // 4) GET /api/v1/mcf/etat
        $results['checks']['mcf_etat'] = $this->tryCall(function () use ($device) {
            return $device->state();
        }, $ok);

        // 5) GET /api/v1/mcf/connexion
        $results['checks']['mcf_connexion'] = $this->tryCall(function () use ($device) {
            return $device->connection();
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
            $this->info('SFE Bridge smoke — ' . $results['bridge_url']);
            foreach ($results['checks'] as $name => $check) {
                $pass = $check['ok'] ?? false;
                $this->line(sprintf(
                    '  [%s] %s %s',
                    $pass ? 'OK' : 'FAIL',
                    $name,
                    $check['error'] ?? ($check['message'] ?? ($check['level'] ?? ''))
                ));
            }
            if ($this->option('payload')) {
                $this->newLine();
                $this->comment('Sample certifier payload shape (not sent):');
                $this->line(json_encode($results['sample_certifier_payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            $this->newLine();
            $this->line($ok
                ? 'Smoke finished: bridge reachable (device may still need config).'
                : 'Smoke finished: bridge unreachable or misconfigured. Start sfe_bridge.exe and check URL/COM.');
        }

        return $ok ? 0 : 1;
    }

    protected function tryCall(callable $fn, bool &$ok): array
    {
        try {
            $data = $fn();
            return ['ok' => true, 'data' => $data];
        } catch (\Throwable $e) {
            $ok = false;
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
