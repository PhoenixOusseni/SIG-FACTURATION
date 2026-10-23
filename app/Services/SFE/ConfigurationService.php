<?php

namespace App\Services\SFE;

use App\Models\Entete;

class ConfigurationService
{
    public function get(): array
    {
        $e = Entete::singleton();
        return [
            'port_com'     => $e->port_com ?: 'COM3',
            'baudrate'     => $e->mcf_baudrate ?: 9600,
            'ifu_vendeur'  => $e->ifu ?: '',
            'sfeid'        => $e->identifiant_sfe ?: '',
            'nim'          => $e->nim_mcf ?: '',
            'license_key'  => 'NATIVE-DRIVER-LICENSED',
        ];
    }

    public function update(array $config): array
    {
        $e = Entete::singleton();
        $updates = [];
        if (isset($config['port_com'])) {
            $updates['port_com'] = $config['port_com'];
        }
        if (isset($config['baudrate'])) {
            $updates['mcf_baudrate'] = (int) $config['baudrate'];
        }
        if (isset($config['ifu_vendeur'])) {
            $updates['ifu'] = $config['ifu_vendeur'];
        }
        if (isset($config['sfeid'])) {
            $updates['identifiant_sfe'] = $config['sfeid'];
        }
        if (isset($config['nim'])) {
            $updates['nim_mcf'] = $config['nim'];
        }

        if (!empty($updates)) {
            $e->update($updates);
        }

        return [
            'succes' => true,
            'message' => 'Configuration du driver série MCF mise à jour avec succès.',
        ];
    }
}
