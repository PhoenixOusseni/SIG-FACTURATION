<?php

namespace Tests\Unit\Comptoir;

use App\Models\Caisse;
use Tests\TestCase;

class CaisseCodeTokenTest extends TestCase
{
    public function test_code_token_sanitizes_for_ticket_codes(): void
    {
        $c = new Caisse(['code' => 'C01']);
        $c->id = 1;
        $this->assertEquals('C01', $c->codeToken());

        $c2 = new Caisse(['code' => 'Caisse-01']);
        $c2->id = 2;
        $this->assertEquals('CAISSE01', $c2->codeToken());

        $c3 = new Caisse(['code' => '@@@']);
        $c3->id = 9;
        $this->assertEquals('C9', $c3->codeToken());
    }
}
