<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Services\SFE\ConfigurationService;
use App\Services\SFE\DeviceStatusService;
use App\Services\SFE\StatusSnapshotService;
use App\Services\SFE\InvoiceSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SfeController extends Controller
{
    public function status(StatusSnapshotService $snapshots)
    {
        $status = Cache::remember('sfe_quick_status', 15, function () use ($snapshots) {
            return $snapshots->current();
        });
        return response()->json($status);
    }
    public function finalize(Facture $facture, InvoiceSigningService $signer)
    {
        try { $signer->sign($facture); smilify('success', 'Facture fiscalisée et verrouillée avec succès.'); }
        catch (\Throwable $e) { report($e); smilify('error', $e->getMessage()); }
        return redirect()->route('module_ordre_facture.show', $facture->id);
    }
    public function config(ConfigurationService $service) { return response()->json($service->get()); }
    public function ports(DeviceStatusService $service) { return response()->json($service->ports()); }
    public function connection(DeviceStatusService $service) { return response()->json($service->connection()); }
    public function report(Request $request, DeviceStatusService $service)
    {
        $request->validate(['type_rapport' => 'required|in:X,Z']);
        return response()->json($service->report($request->type_rapport));
    }
}
