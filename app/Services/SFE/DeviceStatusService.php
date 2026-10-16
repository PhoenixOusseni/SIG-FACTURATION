<?php

namespace App\Services\SFE;

class DeviceStatusService
{
    protected $client;
    public function __construct(BridgeClient $client) { $this->client = $client; }
    public function state(): array { return $this->client->get('/api/v1/mcf/etat'); }
    public function connection(): array { return $this->client->get('/api/v1/mcf/connexion'); }
    public function ports(): array { return $this->client->get('/api/v1/ports'); }
    public function report(string $type): array
    {
        $type = strtoupper($type);
        if (!in_array($type, ['X', 'Z'], true)) throw new \InvalidArgumentException('Le rapport doit être X ou Z.');
        return $this->client->post('/api/v1/mcf/rapport', ['type_rapport' => $type]);
    }
}
