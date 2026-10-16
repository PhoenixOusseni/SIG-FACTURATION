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
        $bridgeJoignable = (bool) ($snapshot['bridge_online'] ?? false);
        $configurationValide = (bool) ($settings->ifu && $settings->denomination && $settings->sfe_bridge_url);
        $pret = $configurationValide && ($device['succes'] ?? false);
        $status = [
            'level' => !$bridgeJoignable ? 'disconnected' : ($pret ? 'connected' : 'warning'),
            'bridge' => $snapshot['bridge'] ?? [],
            'device' => $device,
            'configuration_valid' => $configurationValide,
            'message' => !$bridgeJoignable
                ? 'Bridge SFE hors ligne ou délai dépassé.'
                : ($pret ? 'Facturation fiscale opérationnelle.' : 'Bridge joignable, mais boîtier ou configuration non prêt.'),
        ];
        $certifications = McfCertification::with(['facture.Contribuable'])->orderByDesc('id')->paginate(20);

        return view('pages.mcf.index', compact('status', 'config', 'ports', 'connexion', 'settings', 'certifications'));
    }

    public function configurer(Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['administrateur', 'superviseur']), 403);
        $data = $request->validate([
            'port_com' => 'required|string|max:30', 'baudrate' => 'required|integer|min:1200|max:115200',
            'sfeid' => 'nullable|string|max:100', 'ifu_vendeur' => 'required|string|max:50',
            'license_key' => 'nullable|string|max:255', 'sfe_bridge_url' => 'required|url|max:255',
            'sfe_bridge_timeout' => 'required|integer|min:1|max:120',
        ]);
        try {
            $bridgeData = collect($data)->only(['port_com','baudrate','sfeid','ifu_vendeur','license_key'])->all();
            if (empty($bridgeData['license_key'])) {
                $current = $this->configuration->get();
                $bridgeData['license_key'] = $current['license_key'] ?? '';
            }
            $result = $this->configuration->update($bridgeData);
            if (!($result['succes'] ?? false)) throw new \RuntimeException($result['erreur'] ?? 'Configuration refusée par le bridge.');
            Entete::singleton()->update(['sfe_bridge_url' => $data['sfe_bridge_url'], 'sfe_bridge_timeout' => $data['sfe_bridge_timeout'], 'ifu' => $data['ifu_vendeur'], 'identifiant_sfe' => $data['sfeid']]);
            smilify('success', 'Configuration enregistrée. Utilisez « Tester le boîtier » pour confirmer.');
        } catch (\Throwable $e) { report($e); smilify('error', $e->getMessage()); }
        return redirect()->route('mcf.index');
    }

    public function tester()
    {
        $status = $this->snapshots->refresh();
        if ($status['level'] === 'connected') smilify('success', $status['message']);
        else smilify('error', $status['message'] . ' ' . ($status['device']['erreur'] ?? ''));
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
        try { $this->signer->sign($facture); smilify('success', 'Facture certifiée par le boîtier physique via le bridge SFE.'); }
        catch (\Throwable $e) { report($e); smilify('error', 'Certification refusée : ' . $e->getMessage()); }
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
