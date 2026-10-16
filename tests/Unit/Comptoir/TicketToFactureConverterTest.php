<?php

namespace Tests\Unit\Comptoir;

use App\Models\Caisse;
use App\Models\Categorie;
use App\Models\Contribuable;
use App\Models\ElementTicketComptoir;
use App\Models\ModePaie;
use App\Models\Produit;
use App\Models\ReglementTicket;
use App\Models\Service;
use App\Models\SessionCaisse;
use App\Models\TicketComptoir;
use App\Models\Tva;
use App\Models\User;
use App\Services\Comptoir\TicketMoney;
use App\Services\Comptoir\TicketToFactureConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class TicketToFactureConverterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('ticket_comptoirs') || !Schema::hasTable('factures')) {
            $this->markTestSkipped('Comptoir / facture schema not migrated for integration tests.');
        }
    }

    protected function seedComptoirFixture(float $tvaTaux = 18.0): array
    {
        $user = User::create([
            'email' => 'super-comptoir@test.local',
            'login' => 'supercomptoir',
            'password' => Hash::make('password'),
            'role' => 'Administrateur',
            'nom' => 'Test',
            'prenom' => 'Super',
            'activate' => 1,
            'module' => 'tous',
        ]);

        $service = Service::create([
            'code' => 'DEP1',
            'libelle' => 'Dépôt test',
        ]);

        $caisse = Caisse::create([
            'code' => 'C01',
            'libelle' => 'Caisse test',
            'service_id' => $service->id,
            'active' => true,
        ]);

        $session = SessionCaisse::create([
            'caisse_id' => $caisse->id,
            'service_id' => $service->id,
            'date_metier' => now()->toDateString(),
            'statut' => SessionCaisse::STATUT_OUVERTE,
            'fond_initial' => 0,
            'dernier_numero' => 1,
            'opened_by' => $user->id,
            'opened_at' => now(),
        ]);

        $tva = Tva::create([
            'code' => 'B',
            'intitule' => $tvaTaux . '%',
            'taux' => $tvaTaux,
            'compte_tva' => 44561,
        ]);

        $produit = Produit::create([
            'code' => 'SKU1',
            'designation' => 'Article test',
            'prix_unitaire' => 1000,
            'qte' => 50,
            'tva_id' => $tva->id,
        ]);

        $mode = ModePaie::firstOrCreate(['code' => 'ESP'], ['libelle' => 'Espèce']);

        $cat = Categorie::firstOrCreate(['code' => 'CC'], ['libelle' => 'Client']);
        $client = Contribuable::create([
            'assujeti' => 'Client Conversion',
            'ifu' => '1234567890123',
            'categories_id' => $cat->id,
        ]);

        $money = TicketMoney::computeTicket([
            [
                'quantite' => 2,
                'prix_unitaire' => 1000,
                'tva_taux' => $tvaTaux,
            ],
        ], 0);

        $ticket = TicketComptoir::create([
            'code' => 'FACT-C01-001/' . now()->format('d/m/Y'),
            'numero' => 1,
            'session_caisse_id' => $session->id,
            'caisse_id' => $caisse->id,
            'date_metier' => now()->toDateString(),
            'contribuable_id' => $client->id,
            'user_id' => $user->id,
            'statut' => TicketComptoir::STATUT_PAYE,
            'remise' => 0,
            'montant_ht' => $money['montant_ht'],
            'montant_ttc' => $money['montant_ttc'],
            'stock_moved_at' => now()->subMinute(),
        ]);

        ElementTicketComptoir::create([
            'ticket_comptoir_id' => $ticket->id,
            'produits_id' => $produit->id,
            'designation' => $produit->designation,
            'quantite' => 2,
            'prix_unitaire' => 1000,
            'tva_taux' => $tvaTaux,
            'montant_ht' => $money['lignes'][0]['montant_ht'],
            'montant_tva' => $money['lignes'][0]['montant_tva'],
            'montant_ttc_ligne' => $money['lignes'][0]['montant_ttc_ligne'],
        ]);

        ReglementTicket::create([
            'code' => 'RTK-00001/' . date('Y'),
            'ticket_comptoir_id' => $ticket->id,
            'date' => now()->toDateString(),
            'modepaie_id' => $mode->id,
            'mode' => $mode->libelle,
            'montant' => $money['montant_ttc'],
            'montant_recu' => $money['montant_ttc'],
            'rendu' => 0,
            'users_id' => $user->id,
        ]);

        return compact('user', 'service', 'caisse', 'session', 'tva', 'produit', 'mode', 'client', 'ticket', 'money');
    }

    public function test_happy_path_converts_to_soldee_facture(): void
    {
        $fx = $this->seedComptoirFixture(18.0);
        $stockMovedAt = $fx['ticket']->stock_moved_at->copy();

        $converter = new TicketToFactureConverter();
        $facture = $converter->convert(
            [$fx['ticket']->id],
            ['contribuable_id' => $fx['client']->id],
            $fx['user']
        );

        $this->assertNotNull($facture->id);
        $this->assertStringStartsWith('FAC-', $facture->code);
        $this->assertEquals('soldée', $facture->statut);
        $this->assertLessThanOrEqual(0.009, abs((float) $facture->reste_a_payer));

        $ticket = $fx['ticket']->fresh();
        $this->assertEquals(TicketComptoir::STATUT_CONVERTI, $ticket->statut);
        $this->assertEquals($facture->id, $ticket->facture_id);
        $this->assertEquals(
            $stockMovedAt->format('Y-m-d H:i:s'),
            optional($ticket->stock_moved_at)->format('Y-m-d H:i:s')
        );

        $this->assertEquals(1, $facture->Reglements()->count());
        $this->assertEquals('Espèces', $facture->Reglements()->first()->mode);
    }

    public function test_fail_closed_when_product_tva_drifts(): void
    {
        $fx = $this->seedComptoirFixture(18.0);

        // Drift master TVA after ticket was paid with 18%
        $fx['tva']->update(['taux' => 5]);

        $converter = new TicketToFactureConverter();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Conversion impossible');

        try {
            $converter->convert(
                [$fx['ticket']->id],
                ['contribuable_id' => $fx['client']->id],
                $fx['user']
            );
        } catch (RuntimeException $e) {
            // Tickets must stay paye; no facture orphan if rolled back
            $this->assertEquals(TicketComptoir::STATUT_PAYE, $fx['ticket']->fresh()->statut);
            $this->assertNull($fx['ticket']->fresh()->facture_id);
            throw $e;
        }
    }

    public function test_rejects_non_paid_ticket(): void
    {
        $fx = $this->seedComptoirFixture(18.0);
        $fx['ticket']->update(['statut' => TicketComptoir::STATUT_BROUILLON]);

        $converter = new TicketToFactureConverter();
        $this->expectException(RuntimeException::class);
        $converter->convert(
            [$fx['ticket']->id],
            ['contribuable_id' => $fx['client']->id],
            $fx['user']
        );
    }
}
