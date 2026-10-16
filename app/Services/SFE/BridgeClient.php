<?php

namespace App\Services\SFE;

use App\Exceptions\SFE\BridgeException;
use App\Models\Entete;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BridgeClient
{
    public function getMany(array $paths, int $timeout = 3): array
    {
        $settings = Entete::first();
        $url = rtrim((string) ($settings->sfe_bridge_url ?? config('sfe.url')), '/');
        $timeout = max(1, min($timeout, (int) ($settings->sfe_bridge_timeout ?? config('sfe.timeout'))));

        $responses = Http::pool(function (Pool $pool) use ($paths, $url, $timeout) {
            foreach ($paths as $key => $path) {
                $pool->as($key)->acceptJson()->asJson()->timeout($timeout)->get($url . $path);
            }
        });

        return collect($responses)->map(function (Response $response, $key) {
            $data = $response->json();

            if (!$response->successful() || !is_array($data)) {
                return [
                    'succes' => false,
                    'erreur' => 'Bridge SFE HTTP ' . $response->status(),
                ];
            }

            return $data;
        })->all();
    }

    public function get(string $path): array
    {
        return $this->request('get', $path);
    }

    public function post(string $path, array $payload): array
    {
        return $this->request('post', $path, $payload);
    }

    protected function request(string $method, string $path, array $payload = []): array
    {
        $settings = Entete::first();
        $url = rtrim((string) ($settings->sfe_bridge_url ?? config('sfe.url')), '/');
        // Keep enough time for Laravel to turn bridge failures into a controlled
        // response before PHP's default 30-second execution limit terminates it.
        $timeout = min(25, (int) ($settings->sfe_bridge_timeout ?? config('sfe.timeout')));

        try {
            $request = Http::acceptJson()
                ->asJson()
                ->withOptions(['connect_timeout' => 3])
                ->timeout(max(1, $timeout));
            /** @var Response $response */
            $response = $method === 'get'
                ? $request->get($url . $path)
                : $request->post($url . $path, $payload);
        } catch (ConnectionException $e) {
            throw $this->failure('Bridge SFE hors ligne ou délai dépassé. Vérifiez le service local et l’URL.', $path, [], $e);
        }

        $data = $response->json();
        if (!$response->successful()) {
            $detail = is_array($data) ? ($data['detail'] ?? $data['erreur'] ?? null) : null;
            $message = $response->status() === 422
                ? 'Données refusées par le bridge SFE : ' . $this->validationMessage($detail)
                : 'Erreur bridge SFE HTTP ' . $response->status() . '. Vérifiez le bridge et le boîtier.';
            throw $this->failure($message, $path, ['status' => $response->status(), 'response' => $data]);
        }

        if (!is_array($data)) {
            throw $this->failure('Réponse illisible du bridge SFE.', $path, ['body' => $response->body()]);
        }

        return $data;
    }

    protected function validationMessage($detail): string
    {
        if (!is_array($detail)) return (string) ($detail ?: 'schéma invalide');
        return collect($detail)->map(function ($error) {
            return implode('.', $error['loc'] ?? []) . ' : ' . ($error['msg'] ?? 'invalide');
        })->implode('; ');
    }

    protected function failure(string $message, string $path, array $context = [], \Throwable $previous = null): BridgeException
    {
        $context['endpoint'] = $path;
        Log::error($message, $context);
        return new BridgeException($message, $context, 0, $previous);
    }
}
