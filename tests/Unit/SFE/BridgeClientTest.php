<?php

namespace Tests\Unit\SFE;

use App\Exceptions\SFE\BridgeException;
use App\Services\SFE\BridgeClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BridgeClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_bridge_json()
    {
        Http::fake(['*/api/v1/status' => Http::response(['succes' => true])]);
        $this->assertTrue((new BridgeClient())->get('/api/v1/status')['succes']);
    }

    public function test_it_exposes_actionable_validation_errors()
    {
        Http::fake(['*/api/v1/certifier' => Http::response(['detail' => [[
            'loc' => ['body', 'lignes'], 'msg' => 'field required', 'type' => 'missing',
        ]]], 422)]);
        $this->expectException(BridgeException::class);
        $this->expectExceptionMessage('body.lignes');
        (new BridgeClient())->post('/api/v1/certifier', []);
    }
}
