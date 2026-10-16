<?php

namespace App\Console\Commands;

use App\Services\SFE\StatusSnapshotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorSfe extends Command
{
    protected $signature = 'sfe:monitor';
    protected $description = 'Contrôle la disponibilité du bridge et du boîtier fiscal';
    public function handle(StatusSnapshotService $snapshots)
    {
        $status = $snapshots->refresh();
        if ($status['level'] !== 'connected') Log::warning('Surveillance SFE: ' . $status['message'], $status);
        $this->line($status['message']);
        return $status['level'] === 'disconnected' ? 1 : 0;
    }
}
