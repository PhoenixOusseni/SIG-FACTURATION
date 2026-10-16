<?php

namespace App\Services\SFE;

class ConfigurationService
{
    protected $client;
    public function __construct(BridgeClient $client) { $this->client = $client; }
    public function get(): array { return $this->client->get('/api/v1/config'); }
    public function update(array $config): array { return $this->client->post('/api/v1/config', $config); }
}
