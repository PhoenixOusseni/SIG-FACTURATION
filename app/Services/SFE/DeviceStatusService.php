<?php

namespace App\Services\SFE;

use App\Services\Mcf\McfSerialDriver;

class DeviceStatusService
{
    protected McfSerialDriver $driver;

    public function __construct(McfSerialDriver $driver)
    {
        $this->driver = $driver;
    }

    public function state(): array
    {
        return $this->driver->lireEtat();
    }

    public function connection(): array
    {
        return $this->driver->lireEtatConnexion();
    }

    public function ports(): array
    {
        return $this->driver->listerPortsDisponibles();
    }

    public function report(string $type): array
    {
        $type = strtoupper($type);
        if (!in_array($type, ['X', 'Z'], true)) {
            throw new \InvalidArgumentException('Le rapport fiscal doit être de type X ou Z.');
        }

        return $this->driver->genererRapport($type);
    }
}
