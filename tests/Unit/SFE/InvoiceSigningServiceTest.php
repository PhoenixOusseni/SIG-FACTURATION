<?php

namespace Tests\Unit\SFE;

use App\Exceptions\SFE\BridgeException;
use App\Models\Categorie;
use App\Models\Contribuable;
use App\Models\ElementFacture;
use App\Models\Entete;
use App\Models\Facture;
use App\Models\FactureAvoir;
use App\Models\ElementFactureAvoir;
use App\Models\ModePaie;
use App\Models\Produit;
use App\Models\Tva;
use App\Models\Typefacture;
use App\Services\SFE\InvoiceSigningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceSigningServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('factures')) {
            $this->markTestSkipped('Database schema not available for integration-style unit tests.');
        }

        Entete::create([
            'denomination' => 'Test SARL',
            'ifu' => '00001234A',
            'identifiant_sfe' => '00656',
            'sfe_bridge_url' => 'http://127.0.0.1:8000',
            'sfe_bridge_timeout' => 5,
        ]);
    }

    protected function fakeSuccessResponse(array $extra = []): array
    {
        return array_merge([
            'succes' => true,
            'erreur' => null,
            'signature' => 'TESTSIG' . strtoupper(substr(md5(microtime()), 0, 16)),
            'qrcode_data' => 'BFSECEF01;EL02000018-1;TESTSIG;00183328k;20260622131443',
            'nim' => 'EL02000018-1',
            'date_certification' => '20260622131443',
            'psvb' => 0.0,
            'compteur_total' => '1255',
            'compteur_type' => '374',
        ], $extra);
    }

    protected function makeSale(string $typeCode = 'FV'): Facture
    {
        $type = Typefacture::firstOrCreate(['code' => $typeCode], ['libelle' => $typeCode]);
        $mode = ModePaie::firstOrCreate(['code' => 'ESP'], ['libelle' => 'Espèce']);
        $tva = Tva::firstOrCreate(['code' => 'B'], ['intitule' => '18%', 'taux' => 18, 'compte_tva' => 44561]);
        $categorie = Categorie::first() ?: Categorie::create([
            'code' => 'C1',
            'libelle' => 'Client test',
        ]);
        $clientAttrs = [
            'assujeti' => 'Client Test',
            'ifu' => '00009999B',
            'telephone' => '70000000',
            'adresse' => 'Ouaga',
            'categories_id' => $categorie->id,
        ];
        if (\Schema::hasColumn('contribuables', 'type_client')) {
            $clientAttrs['type_client'] = 'PM';
        }
        if (\Schema::hasColumn('contribuables', 'email')) {
            $clientAttrs['email'] = 'contact@test.bf';
        }
        $client = Contribuable::create($clientAttrs);
        $produitAttrs = [
            'code' => 'P1',
            'designation' => 'Produit test',
            'prix_unitaire' => 1000,
            'qte' => 100,
            'tva_id' => $tva->id,
        ];
        if (\Schema::hasColumn('produits', 'famille_produit')) {
            $produitAttrs['famille_produit'] = 'LOCBIE';
        }
        $produit = Produit::create($produitAttrs);

        $facture = Facture::create([
            'code' => 'FAC-00099/2026',
            'date' => now()->toDateString(),
            'statut' => 'Brouillon',
            'contribuable_id' => $client->id,
            'typefacture_id' => $type->id,
            'modepaie_id' => $mode->id,
            'remise_1' => 0,
            'remise_2' => 0,
        ]);

        ElementFacture::create([
            'facture_id' => $facture->id,
            'produits_id' => $produit->id,
            'quantite' => 2,
            'prix_unitaire' => 1000,
            'designation' => 'Produit test',
        ]);

        return $facture->fresh(['ElementFactures.Produit.tva', 'Contribuable', 'ModePaie', 'Typefacture']);
    }

    protected function makeAvoir(Facture $facture, string $nature, bool $withOriginal = true): FactureAvoir
    {
        $motifs = ['COR' => 'erreur', 'RAN' => 'annulation', 'RAM' => 'retour', 'RRR' => 'remise'];
        $avoir = FactureAvoir::create([
            'code' => 'AVR-' . $nature . '-' . $facture->id,
            'date' => now()->toDateString(),
            'statut' => 'en attente',
            'motif' => $motifs[$nature],
            'nature_avoir' => $nature,
            'contribuables_id' => $facture->contribuable_id,
            'facture_id' => $withOriginal ? $facture->id : null,
            'remise_1' => 0,
            'remise_2' => 0,
        ]);

        $line = ElementFacture::where('facture_id', $facture->id)->firstOrFail();
        ElementFactureAvoir::create([
            'facture_avoir_id' => $avoir->id,
            'produits_id' => $line->produits_id,
            'quantite' => $line->quantite,
            'prix_unitaire' => $line->prix_unitaire,
            'montant_total' => $line->quantite * $line->prix_unitaire,
        ]);

        return $avoir->fresh();
    }

    public static function saleTypeProvider(): array
    {
        return [
            'FV standard sale' => ['FV', 'FV'],
            'FT advance maps to FV' => ['FT', 'FV'],
            'EV export sale' => ['EV', 'EV'],
            'ET export advance maps to EV' => ['ET', 'EV'],
        ];
    }

    /** @dataProvider saleTypeProvider */
    public function test_all_sale_signing_types(string $applicationType, string $bridgeType)
    {
        $facture = $this->makeSale($applicationType);
        Http::fake(['*/api/v1/certifier' => Http::response(
            $this->fakeSuccessResponse(['signature' => 'SIGSALE' . $applicationType . '0000000001']),
            200
        )]);

        $certification = app(InvoiceSigningService::class)->signFacture($facture);

        Http::assertSent(function ($request) use ($bridgeType) {
            $body = $request->data();
            return ($body['type_facture'] ?? null) === $bridgeType
                && ($body['nature_avoir'] ?? null) === ''
                && ($body['reference_originale'] ?? null) === '';
        });
        $this->assertSame($bridgeType, $certification->type_facture);
        $this->assertTrue($facture->fresh()->is_fiscal_locked);
    }

    public static function creditTypeProvider(): array
    {
        $cases = [];
        foreach (['COR', 'RAN', 'RAM', 'RRR'] as $nature) {
            $cases['FA ' . $nature] = ['FV', 'FA', $nature];
            $cases['EA ' . $nature] = ['EV', 'EA', $nature];
        }
        return $cases;
    }

    /** @dataProvider creditTypeProvider */
    public function test_all_credit_note_signing_types(string $originalType, string $creditType, string $nature)
    {
        $facture = $this->makeSale($originalType);
        Http::fake(['*/api/v1/certifier' => Http::sequence()
            ->push($this->fakeSuccessResponse([
                'signature' => 'SIGORIGINAL' . $originalType . $nature,
                'nim' => 'EL02000018-1',
                'compteur_type' => '10',
                'compteur_total' => '100',
            ]), 200)
            ->push($this->fakeSuccessResponse([
                'signature' => 'SIGCREDIT' . $creditType . $nature . '000001',
                'compteur_type' => '2',
                'compteur_total' => '101',
            ]), 200),
        ]);

        app(InvoiceSigningService::class)->signFacture($facture);
        $avoir = $this->makeAvoir($facture, $nature);
        $certification = app(InvoiceSigningService::class)->signAvoir($avoir);

        Http::assertSent(function ($request) use ($creditType, $nature) {
            $body = $request->data();
            return ($body['type_facture'] ?? null) === $creditType
                && ($body['nature_avoir'] ?? null) === $nature
                && ($body['reference_originale'] ?? null) === 'EL02000018-1-100';
        });
        $this->assertSame($creditType, $certification->type_facture);
        $this->assertSame($nature, $certification->nature_avoir);
        $this->assertTrue($avoir->fresh()->is_fiscal_locked);
    }

    public function test_rrr_without_original_uses_literal_rrr_reference()
    {
        $facture = $this->makeSale('FV');
        $avoir = $this->makeAvoir($facture, 'RRR', false);
        Http::fake(['*/api/v1/certifier' => Http::response(
            $this->fakeSuccessResponse(['signature' => 'SIGRRRWITHOUTORIGINAL']),
            200
        )]);

        app(InvoiceSigningService::class)->signAvoir($avoir);

        Http::assertSent(function ($request) {
            return ($request->data()['reference_originale'] ?? null) === 'RRR';
        });
    }

    public function test_sign_facture_persists_bridge_type_not_hardcoded_fv()
    {
        $facture = $this->makeSale('EV');
        $payload = $this->fakeSuccessResponse(['signature' => 'SIGEXPORTTEST123456']);

        Http::fake(['*/api/v1/certifier' => Http::response($payload, 200)]);

        $cert = app(InvoiceSigningService::class)->signFacture($facture);

        $this->assertSame('EV', $cert->type_facture);
        $this->assertTrue($facture->fresh()->is_fiscal_locked);
        $this->assertNotNull($facture->fresh()->certification_active);
        $this->assertSame('EV', $facture->fresh()->certification_active->type_facture);
        $this->assertSame('SIGEXPORTTEST123456', $facture->fresh()->dgi_signature);
    }

    public function test_sign_facture_maps_acompte_ft_to_fv()
    {
        $facture = $this->makeSale('FT');
        $payload = $this->fakeSuccessResponse(['signature' => 'SIGACOMPTEFT000001']);

        Http::fake(function ($request) use ($payload) {
            $body = $request->data();
            $this->assertSame('FV', $body['type_facture'] ?? null);
            return Http::response($payload, 200);
        });

        $cert = app(InvoiceSigningService::class)->signFacture($facture);
        $this->assertSame('FV', $cert->type_facture);
    }

    /**
     * Golden shape vs Bridge manuel déploiement (POST /api/v1/certifier sample).
     */
    public function test_sale_payload_matches_bridge_documented_keys()
    {
        $facture = $this->makeSale('FV');
        $payload = $this->fakeSuccessResponse([
            'signature' => 'SIGGOLDENPAYLOAD0001',
            'psvb' => 12.5,
        ]);

        Http::fake(['*/api/v1/certifier' => Http::response($payload, 200)]);

        $cert = app(InvoiceSigningService::class)->signFacture($facture);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $required = [
                'numero_interne', 'type_facture', 'mode_prix', 'categorie_psvb',
                'nature_avoir', 'reference_originale', 'client', 'lignes', 'paiements',
            ];
            foreach ($required as $key) {
                if (!array_key_exists($key, $body)) {
                    return false;
                }
            }
            if ($body['type_facture'] !== 'FV' || $body['mode_prix'] !== 'TTC') {
                return false;
            }
            if ($body['nature_avoir'] !== '' || $body['reference_originale'] !== '') {
                return false;
            }
            $clientKeys = ['type_client', 'nom', 'telephone', 'adresse', 'ifu', 'rccm', 'email'];
            foreach ($clientKeys as $ck) {
                if (!array_key_exists($ck, $body['client'] ?? [])) {
                    return false;
                }
            }
            if (!in_array($body['client']['type_client'], ['CC', 'PM', 'PP', 'PC'], true)) {
                return false;
            }
            $line = $body['lignes'][0] ?? [];
            foreach (['designation', 'quantite', 'prix_unitaire', 'type_article_dgi', 'groupe_taxe', 'taux_tva', 'montant_ts'] as $lk) {
                if (!array_key_exists($lk, $line)) {
                    return false;
                }
            }
            $pay = $body['paiements'][0] ?? [];
            return isset($pay['mode'], $pay['montant']) && $pay['montant'] > 0;
        });

        if (\Schema::hasColumn('mcf_certifications', 'psvb')) {
            $this->assertEquals(12.5, (float) $cert->psvb);
        }
    }

    public function test_double_sign_is_rejected()
    {
        $facture = $this->makeSale('FV');
        $payload = $this->fakeSuccessResponse(['signature' => 'SIGFIRSTPASS000001']);
        Http::fake(['*/api/v1/certifier' => Http::response($payload, 200)]);

        $service = app(InvoiceSigningService::class);
        $service->signFacture($facture);

        $this->expectException(BridgeException::class);
        $service->signFacture($facture->fresh());
    }

    public function test_sign_avoir_sends_fa_nature_and_reference()
    {
        $facture = $this->makeSale('FV');
        $salePayload = $this->fakeSuccessResponse([
            'signature' => 'SIGORIGINALSALE001',
            'nim' => 'EL02000018-1',
            'compteur_type' => '10',
            'compteur_total' => '100',
        ]);
        $avoirPayload = $this->fakeSuccessResponse([
            'signature' => 'SIGAVOIRRAN0000001',
            'compteur_type' => '2',
            'compteur_total' => '101',
        ]);

        Http::fake([
            '*/api/v1/certifier' => Http::sequence()
                ->push($salePayload, 200)
                ->push($avoirPayload, 200),
        ]);

        app(InvoiceSigningService::class)->signFacture($facture);

        $avoir = FactureAvoir::create([
            'code' => 'AVR-00001/2026',
            'date' => now()->toDateString(),
            'statut' => 'en attente',
            'motif' => 'annulation',
            'nature_avoir' => 'RAN',
            'contribuables_id' => $facture->contribuable_id,
            'facture_id' => $facture->id,
            'remise_1' => 0,
            'remise_2' => 0,
        ]);

        $line = ElementFacture::where('facture_id', $facture->id)->first();
        ElementFactureAvoir::create([
            'facture_avoir_id' => $avoir->id,
            'produits_id' => $line->produits_id,
            'quantite' => $line->quantite,
            'prix_unitaire' => $line->prix_unitaire,
            'montant_total' => $line->quantite * $line->prix_unitaire,
        ]);

        $cert = app(InvoiceSigningService::class)->signAvoir($avoir->fresh());

        Http::assertSent(function ($request) {
            $body = $request->data();
            return ($body['type_facture'] ?? null) === 'FA'
                && ($body['nature_avoir'] ?? null) === 'RAN'
                && ($body['reference_originale'] ?? null) === 'EL02000018-1-100';
        });

        $this->assertSame('FA', $cert->type_facture);
        $this->assertSame('RAN', $cert->nature_avoir);
        $this->assertSame($avoir->id, $cert->facture_avoir_id);
        $this->assertTrue($avoir->fresh()->is_fiscal_locked);
        $this->assertTrue($facture->fresh()->is_fiscal_locked);
    }
    public function test_zero_quantity_is_rejected_before_bridge_call()
    {
        $facture = $this->makeSale();
        $facture->ElementFactures()->first()->update(['quantite' => 0]);
        Http::fake();

        try {
            app(InvoiceSigningService::class)->signFacture($facture->fresh());
            $this->fail('Une quantité nulle aurait dû être refusée.');
        } catch (BridgeException $e) {
            $this->assertStringContainsString('quantité doit être supérieure à zéro', $e->getMessage());
        }
        Http::assertNothingSent();
    }

    public function test_invalid_client_ifu_is_rejected_before_bridge_call()
    {
        $facture = $this->makeSale();
        $facture->Contribuable->update(['ifu' => 'IFU-INVALIDE']);
        Http::fake();

        try {
            app(InvoiceSigningService::class)->signFacture($facture->fresh());
            $this->fail('Un IFU invalide aurait dû être refusé.');
        } catch (BridgeException $e) {
            $this->assertStringContainsString('8 chiffres suivis', $e->getMessage());
        }
        Http::assertNothingSent();
    }

    public function test_partial_ran_is_rejected_without_second_bridge_call()
    {
        $facture = $this->makeSale();
        Http::fake(['*/api/v1/certifier' => Http::response($this->fakeSuccessResponse([
            'signature' => 'SIG-SALE-FOR-RAN',
            'compteur_total' => '200',
        ]), 200)]);
        app(InvoiceSigningService::class)->signFacture($facture);

        $avoir = $this->makeAvoir($facture->fresh(), 'RAN');
        $avoir->ElementFactureAvoirs()->first()->update(['quantite' => 1, 'montant_total' => 1000]);
        try {
            app(InvoiceSigningService::class)->signAvoir($avoir->fresh());
            $this->fail('Une annulation RAN partielle aurait dû être refusée.');
        } catch (BridgeException $e) {
            $this->assertStringContainsString('exactement égal au solde fiscal', $e->getMessage());
        }
        Http::assertSentCount(1);
    }

    public function test_device_tr_code_is_translated_for_ui()
    {
        $facture = $this->makeSale();
        Http::fake(['*/api/v1/certifier' => Http::response([
            'succes' => false,
            'erreur' => 'Erreur article : TR',
        ], 200)]);

        try {
            app(InvoiceSigningService::class)->signFacture($facture);
            $this->fail('Le refus TR aurait dû lever une exception.');
        } catch (BridgeException $e) {
            $this->assertStringContainsString('Article refuse par le SFE', $e->getMessage());
            $this->assertStringContainsString('Erreur article : TR', $e->getMessage());
        }
    }
}
