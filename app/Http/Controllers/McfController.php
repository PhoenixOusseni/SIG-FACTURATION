<?php

namespace App\Http\Controllers;

use App\Models\Entete;
use App\Models\Facture;
use App\Models\McfCertification;
use App\Services\SFE\ConfigurationService;
use App\Services\SFE\StatusSnapshotService;
use App\Services\SFE\DeviceStatusService;
use App\Services\SFE\InvoiceSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class McfController extends Controller
{
    protected $device;
    protected $configuration;
    protected $signer;
    protected $snapshots;

    public function __construct(DeviceStatusService $device, ConfigurationService $configuration, InvoiceSigningService $signer, StatusSnapshotService $snapshots)
    {
        $this->device = $device;
        $this->configuration = $configuration;
        $this->signer = $signer;
        $this->snapshots = $snapshots;
    }

    public function index()
    {
        $settings = Entete::singleton();
        $snapshot = $this->snapshots->current();

        $device = $snapshot['device'] ?? ['succes' => false];
        $config = $snapshot['config'] ?? [];
        $ports = $snapshot['ports'] ?? [];
        $connexion = $snapshot['connexion'] ?? ['succes' => false];
        $bridgeJoignable = true;
        $configurationValide = (bool) ($settings->ifu && $settings->denomination);
        $pret = $configurationValide && ($device['succes'] ?? false);
        $status = [
            'level' => $pret ? 'connected' : 'warning',
            'bridge' => $snapshot['bridge'] ?? [],
            'device' => $device,
            'configuration_valid' => $configurationValide,
            'message' => $pret
                ? ($snapshot['message'] ?? 'Facturation fiscale opérationnelle.')
                : 'Configuration incomplète ou boîtier non prêt.',
            'dgi_days_since' => $snapshot['dgi_days_since'] ?? 0,
            'dgi_last_sync' => $snapshot['dgi_last_sync'] ?? now()->format('d/m/Y à H:i:s'),
            'warn_7_days' => $snapshot['warn_7_days'] ?? false,
        ];
        $certifications = McfCertification::with(['facture.Contribuable'])->orderByDesc('id')->take(500)->get();

        return view('pages.mcf.index', compact('status', 'config', 'ports', 'connexion', 'settings', 'certifications'));
    }

    public function configurer(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['administrateur', 'superviseur']), 403);
        $data = $request->validate([
            'port_com' => 'required|string|max:30',
            'baudrate' => 'required|integer|min:1200|max:115200',
            'sfeid' => 'nullable|string|max:100',
            'ifu_vendeur' => 'required|string|max:50',
            'license_key' => 'nullable|string|max:255',
            'sfe_bridge_url' => 'nullable|string|max:255',
            'sfe_bridge_timeout' => 'nullable|integer|min:1|max:120',
        ]);
        try {
            $this->configuration->update([
                'port_com' => $data['port_com'],
                'baudrate' => $data['baudrate'],
                'ifu_vendeur' => $data['ifu_vendeur'],
                'sfeid' => $data['sfeid'],
            ]);
            Entete::singleton()->update([
                'port_com' => $data['port_com'],
                'mcf_baudrate' => $data['baudrate'],
                'ifu' => $data['ifu_vendeur'],
                'identifiant_sfe' => $data['sfeid'],
            ]);
            $this->snapshots->refresh();
            smilify('success', 'Configuration du pilote MCF Eltrade enregistrée avec succès.');
        } catch (\Throwable $e) { report($e); smilify('error', $e->getMessage()); }
        return redirect()->route('mcf.index');
    }

    public function tester(\App\Services\Mcf\McfSerialDriver $driver)
    {
        $res = $driver->testerLiaisonSerieReelle();
        $this->snapshots->refresh();

        if ($res['succes']) {
            if (!empty($res['hardware'])) {
                smilify('success', '🟢 Boîtier physique connecté ! ' . $res['message']);
            } else {
                smilify('warning', '🟡 Mode Simulation actif : ' . $res['message']);
            }
        } else {
            smilify('error', '🔴 ' . ($res['erreur'] ?? 'Le boîtier MCF ne répond pas sur le port série configuré.'));
        }

        return redirect()->route('mcf.index');
    }

    public function autoConnect(\App\Services\Mcf\McfSerialDriver $driver)
    {
        abort_unless(in_array(auth()->user()->role, ['administrateur', 'superviseur']), 403);
        try {
            $res = $driver->detecterEtConnecterPortAuto();
            if ($res['succes']) {
                $this->snapshots->refresh();
                \App\Models\AuditLog::enregistrer('UPDATE', 'MCF', "Détection et connexion automatique sur {$res['port']} (NIM: {$res['nim']})");
                smilify('success', $res['message']);
            } else {
                smilify('error', $res['message']);
            }
        } catch (\Throwable $e) {
            report($e);
            smilify('error', 'Erreur de détection automatique : ' . $e->getMessage());
        }
        return redirect()->route('mcf.index');
    }

    public function scanPorts(\App\Services\Mcf\McfSerialDriver $driver)
    {
        abort_unless(in_array(auth()->user()->role, ['administrateur', 'superviseur']), 403);
        $ports = $driver->listerPortsDisponibles(true);
        smilify('success', count($ports) . ' port(s) COM détecté(s) sur la machine.');
        return redirect()->route('mcf.index');
    }

    public function rapport(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['administrateur', 'superviseur']), 403);
        $request->validate(['type_rapport' => 'required|in:X,Z']);
        try {
            $result = $this->device->report($request->type_rapport);
            if (!($result['succes'] ?? false)) throw new \RuntimeException($result['erreur'] ?? 'Rapport refusé.');
            smilify('success', $result['message'] ?? ('Rapport ' . $request->type_rapport . ' généré.'));
        } catch (\Throwable $e) { report($e); smilify('error', $e->getMessage()); }
        return redirect()->route('mcf.index');
    }

    public function certifier($factureId)
    {
        $facture = Facture::findOrFail($factureId);
        try {
            $this->signer->sign($facture);
            smilify('success', 'Facture certifiée avec succès par le pilote MCF Eltrade (Norme SECeF ST 1.0).');
        } catch (\Throwable $e) {
            report($e);
            smilify('error', 'Certification refusée : ' . $e->getMessage());
        }
        return redirect()->route('module_ordre_facture.show', $facture->id);
    }

    public function annuler(Request $request, $certificationId)
    {
        $certification = McfCertification::findOrFail($certificationId);
        smilify('error', 'L’annulation directe est interdite. Créez et fiscalisez une facture d’avoir liée à la facture originale.');
        return redirect()->route('module_ordre_facture.show', $certification->facture_id);
    }

    protected function safe(callable $operation, array $fallback): array
    {
        try { return $operation(); } catch (\Throwable $e) { report($e); $fallback['erreur'] = $e->getMessage(); return $fallback; }
    }
}
