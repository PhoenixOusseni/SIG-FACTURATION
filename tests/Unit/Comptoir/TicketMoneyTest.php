<?php

namespace Tests\Unit\Comptoir;

use App\Services\Comptoir\TicketMoney;
use Tests\TestCase;

class TicketMoneyTest extends TestCase
{
    public function test_compute_ticket_ht_tva_remise_ttc(): void
    {
        $result = TicketMoney::computeTicket([
            [
                'produits_id' => 1,
                'quantite' => 2,
                'prix_unitaire' => 1000,
                'tva_taux' => 18,
            ],
            [
                'produits_id' => 2,
                'quantite' => 1,
                'prix_unitaire' => 500,
                'tva_taux' => 0,
            ],
        ], 100);

        // Line1: HT 2000, TVA 360, TTC 2360
        // Line2: HT 500, TVA 0, TTC 500
        // Brut 2860 - remise 100 = 2760
        $this->assertEquals(2500.0, $result['montant_ht']);
        $this->assertEquals(360.0, $result['montant_tva']);
        $this->assertEquals(2860.0, $result['montant_ttc_brut']);
        $this->assertEquals(2760.0, $result['montant_ttc']);
        $this->assertEquals(100.0, $result['remise']);
        $this->assertCount(2, $result['lignes']);
    }

    public function test_nearly_equal_uses_tolerance(): void
    {
        $this->assertTrue(TicketMoney::nearlyEqual(100.0, 100.005));
        $this->assertFalse(TicketMoney::nearlyEqual(100.0, 100.02));
    }
}
