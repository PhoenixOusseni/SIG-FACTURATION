<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\FactureAvoir;
use App\Services\SFE\InvoiceSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CertificationController extends Controller
{
    protected $signer;

    public function __construct(InvoiceSigningService $signer)
    {
        $this->signer = $signer;
    }

    public function certifier(Request $request)
    {
        $hasFacture = $request->filled('facture_id');
        $hasAvoir = $request->filled('facture_avoir_id');

        if ($hasFacture === $hasAvoir) {
            throw ValidationException::withMessages([
                'facture_id' => 'Fournissez facture_id ou facture_avoir_id (un seul).',
            ]);
        }

        if ($hasAvoir) {
            return $this->certifierAvoir($request);
        }

        $validated = $request->validate(['facture_id' => 'required|integer|exists:factures,id']);
        $facture = Facture::findOrFail($validated['facture_id']);

        try {
            $certification = $this->signer->signFacture($facture);

            return response()->json([
                'success' => true,
                'message' => 'Facture fiscalisée et verrouillée avec succès.',
                'facture' => [
                    'id' => $facture->id,
                    'code' => $facture->code,
                    'statut' => 'Fiscalisée',
                    'type' => $certification->type_facture,
                ],
                'fiscal' => [
                    'signature' => $certification->sig,
                    'nim' => $certification->nim,
                    'type_facture' => $certification->type_facture,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            Log::warning('Fiscalisation SFE refusée', ['facture_id' => $facture->id, 'message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'action' => 'La facture reste en brouillon. Corrigez les données indiquées puis relancez la fiscalisation.',
            ], 422);
        }
    }

    protected function certifierAvoir(Request $request)
    {
        $validated = $request->validate([
            'facture_avoir_id' => 'required|integer|exists:facture_avoirs,id',
        ]);
        $avoir = FactureAvoir::findOrFail($validated['facture_avoir_id']);

        try {
            $certification = $this->signer->signAvoir($avoir);

            return response()->json([
                'success' => true,
                'message' => 'Avoir fiscalisé et verrouillé avec succès.',
                'facture_avoir' => [
                    'id' => $avoir->id,
                    'code' => $avoir->code,
                    'statut' => 'Fiscalisée',
                    'type' => $certification->type_facture,
                    'nature_avoir' => $certification->nature_avoir,
                ],
                'fiscal' => [
                    'signature' => $certification->sig,
                    'nim' => $certification->nim,
                    'type_facture' => $certification->type_facture,
                    'nature_avoir' => $certification->nature_avoir,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            Log::warning('Fiscalisation avoir SFE refusée', [
                'facture_avoir_id' => $avoir->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'action' => 'L\'avoir reste en attente. Corrigez les données puis relancez la fiscalisation.',
            ], 422);
        }
    }
}
