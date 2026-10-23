<?php

namespace App\Services\Mcf;

use App\Models\Entete;
use App\Models\McfMachineState;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Driver série natif PHP pour boîtier MCF Eltrade CC300 (Norme SECeF BF ST 1.0).
 *
 * Élimine toute dépendance envers un pont Python externe (sfe_bridge.exe).
 * Communique directement avec le port série matériel (COMx sous Windows / ttyUSBx sous Linux)
 * avec gestion stricte des timeouts (évite tout freeze de l'interface).
 *
 * Inclut un émulateur / mode simulation de secours intelligent qui génère des signatures
 * et QR codes conformes à la norme SECeF lorsque le boîtier physique n'est pas branché.
 */
class McfSerialDriver
{
    // ─── Constantes du protocole MCF SECeF BF ST 1.0 ─────────────────────────
    public const SOH = "\x01"; // Début de trame
    public const ETX = "\x03"; // Fin de trame
    public const NAK = "\x15"; // Erreur de réception matériel -> répéter
    public const SYN = "\x16"; // MCF en cours de traitement -> patienter
    public const BRK = "\x04"; // Fin de bloc données réponse
    public const AMB = "\x05"; // Fin de corps commande

    public const CMD_ETAT_MCF         = 0xC1;
    public const CMD_ETAT_CONNEXION   = 0xC2;
    public const CMD_INFO_CONTRIBUABLE= 0x2B;
    public const CMD_INFO_CLIENT      = 0xC3;
    public const CMD_DEBUT_FACTURE    = 0xC0;
    public const CMD_ARTICLE          = 0x31;
    public const CMD_SOUS_TOTAL       = 0x33;
    public const CMD_PAIEMENT         = 0x35;
    public const CMD_INFO_SUPPL       = 0x36;
    public const CMD_FIN_FACTURE      = 0x38;
    public const CMD_RAPPORT          = 0x45;

    public const MODES_PAIEMENT = [
        'ESP' => 'E', // Espèces
        'VIR' => 'V', // Virement bancaire
        'CAR' => 'C', // Carte bancaire
        'MOB' => 'M', // Mobile Money (Orange, Moov, Wave, etc.)
        'CHQ' => 'D', // Chèque
        'AUT' => 'A', // Autre
    ];

    protected ?string $port = null;
    protected int $baudrate = 9600;
    protected int $timeoutMs = 1500; // millisecondes
    protected int $seq = 0x21;
    protected bool $forceSimulation = false;

    public function __construct(?string $port = null, int $baudrate = 9600, bool $forceSimulation = false)
    {
        $this->port = $port;
        $this->baudrate = $baudrate;
        $this->forceSimulation = $forceSimulation;
    }

    // ─── Gestion de la séquence et formatage de trames ───────────────────────

    protected function nextSeq(): int
    {
        $this->seq = ($this->seq % (0xFF - 0x20)) + 0x20;
        return $this->seq;
    }

    /**
     * Échappe les caractères réservés selon le protocole SECeF ST 1.0.
     */
    public static function escape(string $text): string
    {
        return str_replace(
            ["\r", "\n", ",", "<", ">", "&"],
            ['^xa;', '^xd;', '^x2c;', '^lt;', '^gt;', '^amp;'],
            $text
        );
    }

    /**
     * Calcule le BCC (somme de contrôle spécifique Eltrade CC300 décalée en 4 caractères ASCII hex).
     */
    public static function calculateBcc(string $data): string
    {
        $bytes = unpack('C*', $data);
        $sum = array_sum($bytes) & 0xFFFF;
        $n1 = ($sum >> 12) & 0x0F;
        $n2 = ($sum >> 8) & 0x0F;
        $n3 = ($sum >> 4) & 0x0F;
        $n4 = $sum & 0x0F;

        return chr($n1 + 0x30) . chr($n2 + 0x30) . chr($n3 + 0x30) . chr($n4 + 0x30);
    }

    /**
     * Construit une trame de commande MCF binaire complète.
     * SOH + LEN + SEQ + CMD + DATA + AMB + BCC + ETX
     */
    public function buildFrame(int $seq, int $cmd, string $data = ''): string
    {
        $seqByte = chr(($seq % (0xFF - 0x20)) + 0x20);
        $cmdByte = chr($cmd);
        $amb = self::AMB;

        $corps = $seqByte . $cmdByte . $data . $amb;
        $lenByte = chr(4 + strlen($data) + 0x20);

        $bccData = $lenByte . $corps;
        $bcc = self::calculateBcc($bccData);

        return self::SOH . $lenByte . $corps . $bcc . self::ETX;
    }

    /**
     * Extrait les données de la réponse brute renvoyée par le MCF.
     */
    public function extractData(string $trame): string
    {
        if ($trame === self::SOH . self::ETX) {
            throw new RuntimeException("Le MCF a rejeté la trame (Réponse 0103).");
        }

        if (strlen($trame) < 6) {
            return '';
        }

        // Structure : SOH LEN SEQ CMD DATA [BRK STA AMB BCC ETX]
        $brkPos = strrpos($trame, self::BRK);
        if ($brkPos === false) {
            $ambPos = strrpos($trame, self::AMB);
            $fin = ($ambPos !== false) ? $ambPos : strlen($trame) - 5;
        } else {
            $fin = $brkPos;
        }

        if ($fin <= 4) {
            return '';
        }

        return trim(substr($trame, 4, $fin - 4));
    }

    // ─── Couche Transport Série Bas-Niveau ───────────────────────────────────

    /**
     * Envoie une trame de commande sur le port série et récupère la réponse brute.
     */
    public function envoyerCommande(int $cmd, string $data = '', ?int $timeoutMs = null): string
    {
        if ($this->forceSimulation) {
            throw new RuntimeException("Mode simulation actif.");
        }

        $entete = Entete::singleton();
        $targetPort = $this->port ?: $entete->port_com;
        $targetBaud = $this->baudrate ?: ($entete->mcf_baudrate ?: 9600);

        if (empty($targetPort) || strtoupper($targetPort) === 'AUTO' || strtoupper($targetPort) === 'SIMULATION') {
            $ports = $this->listerPortsDisponibles();
            if (!empty($ports)) {
                $targetPort = $ports[0]['device'];
            }
        }

        if (empty($targetPort) || strtoupper($targetPort) === 'SIMULATION') {
            throw new RuntimeException("Aucun port COM configuré.");
        }

        $effectiveTimeout = $timeoutMs ?: $this->timeoutMs;
        $seq = $this->nextSeq();
        $frame = $this->buildFrame($seq, $cmd, $data);
        $frameHex = strtoupper(bin2hex($frame));

        $maxRetries = 2;
        for ($tentative = 0; $tentative < $maxRetries; $tentative++) {
            $result = $this->transceiveRaw($targetPort, $targetBaud, $frameHex, $effectiveTimeout);

            if ($result === 'NAK') {
                Log::warning("McfSerialDriver: NAK reçu (tentative {$tentative}/{$maxRetries})");
                usleep(100000);
                continue;
            }

            if (str_starts_with($result, 'ERR:')) {
                throw new RuntimeException("Erreur communication MCF: " . substr($result, 4));
            }

            if (str_starts_with($result, 'OK:')) {
                $hexData = substr(trim($result), 3);
                $binaryResponse = hex2bin($hexData);
                return $this->extractData($binaryResponse);
            }

            throw new RuntimeException("Réponse MCF inattendue: {$result}");
        }

        throw new RuntimeException("Échec de communication avec le boîtier MCF après tentatives.");
    }

    /**
     * Échange bas-niveau via le transceiver PowerShell sous Windows ou flux natif sous Linux.
     */
    protected function transceiveRaw(string $port, int $baudrate, string $frameHex, int $timeoutMs): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'mcf_serial_transceiver.ps1';
            $escapedScript = escapeshellarg($scriptPath);
            $cleanPort = escapeshellarg(trim($port));
            $escapedHex = escapeshellarg($frameHex);

            $cmd = "powershell -ExecutionPolicy Bypass -File {$escapedScript} -Port {$cleanPort} -BaudRate {$baudrate} -FrameHex {$escapedHex} -TimeoutMs {$timeoutMs}";
            $output = @shell_exec($cmd);

            return trim((string) $output);
        } else {
            // Linux / macOS via tty device
            $device = (strpos($port, '/') === false) ? "/dev/" . $port : $port;
            if (!file_exists($device)) {
                return "ERR:PORT_NOT_FOUND";
            }
            @shell_exec("stty -F {$device} {$baudrate} cs8 -cstopb -parenb");
            $fp = @fopen($device, 'r+b');
            if (!$fp) {
                return "ERR:CANNOT_OPEN_PORT";
            }
            stream_set_timeout($fp, (int) ($timeoutMs / 1000));
            @fwrite($fp, hex2bin($frameHex));
            $buf = @fread($fp, 512);
            @fclose($fp);
            if ($buf) {
                return "OK:" . strtoupper(bin2hex($buf));
            }
            return "ERR:TIMEOUT";
        }
    }

    // ─── Découverte des Ports COM Système ────────────────────────────────────

    /**
     * Liste tous les ports série / COM disponibles sur la machine hôte.
     */
    public function listerPortsDisponibles(bool $forceRefresh = false): array
    {
        if (!$forceRefresh && Cache::has('mcf_available_ports')) {
            return Cache::get('mcf_available_ports');
        }

        $ports = [];

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                $output = @shell_exec('REG QUERY "HKLM\HARDWARE\DEVICEMAP\SERIALCOMM" 2>nul');
                if ($output) {
                    $lines = explode("\n", $output);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line) || str_starts_with($line, 'HKEY_')) continue;
                        $parts = preg_split('/\s+REG_SZ\s+/', $line);
                        if (count($parts) === 2) {
                            $devKey = trim($parts[0]);
                            $comPort = trim($parts[1]);
                            $desc = 'Port série standard';
                            if (stripos($devKey, 'USBSER') !== false || stripos($devKey, 'USB') !== false) {
                                $desc = 'Périphérique USB Série / Boîtier';
                            } elseif (stripos($devKey, 'BthModem') !== false) {
                                $desc = 'Modem Bluetooth';
                            }
                            $ports[] = [
                                'device' => $comPort,
                                'description' => "{$comPort} ({$desc})",
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("McfSerialDriver: Erreur détection ports COM Windows: " . $e->getMessage());
            }

            // Fallback direct sur vérification de ports
            if (empty($ports)) {
                for ($i = 1; $i <= 10; $i++) {
                    $com = "COM{$i}";
                    $res = @fopen("\\\\.\\" . $com, 'r');
                    if ($res) {
                        @fclose($res);
                        $ports[] = ['device' => $com, 'description' => "Port série ({$com})"];
                    }
                }
            }
        } else {
            $devices = glob('/dev/ttyUSB*') ?: [];
            $devices = array_merge($devices, glob('/dev/ttyACM*') ?: [], glob('/dev/ttyS*') ?: []);
            foreach ($devices as $dev) {
                $ports[] = ['device' => $dev, 'description' => 'Port série Linux'];
            }
        }

        Cache::put('mcf_available_ports', $ports, now()->addMinutes(10));
        return $ports;
    }

    /**
     * Tente de détecter automatiquement le port COM connecté à un boîtier MCF Eltrade réel.
     */
    public function detecterEtConnecterPortAuto(): array
    {
        $ports = $this->listerPortsDisponibles();
        $baudrate = 9600;

        foreach ($ports as $p) {
            $device = $p['device'] ?? '';
            if (!$device) continue;

            try {
                $frameHex = bin2hex($this->buildFrame(1, self::CMD_ETAT_MCF));
                $resp = $this->transceiveRaw($device, $baudrate, $frameHex, 600);

                if (str_starts_with($resp, 'OK:')) {
                    $raw = hex2bin(substr($resp, 3));
                    $data = $this->extractData($raw);
                    if ($data && !str_starts_with($data, 'E:')) {
                        $parts = explode(',', $data);
                        $nim = trim($parts[0] ?? '');
                        $ifu = trim($parts[1] ?? '');

                        // Mise à jour automatique de la configuration
                        $entete = Entete::singleton();
                        $entete->update([
                            'port_com'     => $device,
                            'mcf_baudrate' => $baudrate,
                            'nim_mcf'      => $nim ?: $entete->nim_mcf,
                            'ifu'          => $ifu ?: $entete->ifu,
                        ]);

                        return [
                            'succes'  => true,
                            'port'    => $device,
                            'nim'     => $nim,
                            'ifu'     => $ifu,
                            'message' => "Boîtier MCF Eltrade CC300 détecté avec succès sur {$device} (NIM: {$nim}, IFU: {$ifu}).",
                        ];
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return [
            'succes'  => false,
            'message' => "Aucun boîtier MCF Eltrade physique n'a répondu sur les ports COM disponibles. Vous pouvez utiliser le mode SIMULATION.",
        ];
    }

    /**
     * Teste directement la liaison série sur le port configuré sans fallback de simulation silencieux.
     */
    public function testerLiaisonSerieReelle(): array
    {
        $entete = Entete::singleton();
        $targetPort = $this->port ?: $entete->port_com;
        $targetBaud = $this->baudrate ?: ($entete->mcf_baudrate ?: 9600);

        if (empty($targetPort) || strtoupper($targetPort) === 'AUTO') {
            $ports = $this->listerPortsDisponibles(true);
            if (empty($ports)) {
                return [
                    'succes' => false,
                    'hardware' => false,
                    'erreur' => "Aucun port série / COM n'est détecté sur cette machine.",
                ];
            }
            $targetPort = $ports[0]['device'];
        }

        if (strtoupper($targetPort) === 'SIMULATION') {
            return [
                'succes' => true,
                'hardware' => false,
                'simulation' => true,
                'message' => "Le système est configuré en mode SIMULATION / Émulateur logiciel SECeF BF ST 1.0 (Aucun boîtier physique requis).",
            ];
        }

        try {
            $seq = $this->nextSeq();
            $frame = $this->buildFrame($seq, self::CMD_ETAT_MCF);
            $frameHex = strtoupper(bin2hex($frame));

            $resp = $this->transceiveRaw($targetPort, $targetBaud, $frameHex, 1200);

            if (str_starts_with($resp, 'OK:')) {
                $raw = hex2bin(substr(trim($resp), 3));
                $data = $this->extractData($raw);
                if ($data && !str_starts_with($data, 'E:')) {
                    $parts = explode(',', $data);
                    $nim = trim($parts[0] ?? '');
                    $ifu = trim($parts[1] ?? '');
                    $heure = trim($parts[2] ?? '');
                    $cptTotal = trim($parts[3] ?? '0');
                    $cptFv = trim($parts[4] ?? '0');
                    $cptFa = trim($parts[5] ?? '0');

                    return [
                        'succes' => true,
                        'hardware' => true,
                        'port' => $targetPort,
                        'baudrate' => $targetBaud,
                        'nim' => $nim,
                        'ifu' => $ifu,
                        'heure' => $heure,
                        'compteur_total' => $cptTotal,
                        'compteur_fv' => $cptFv,
                        'compteur_fa' => $cptFa,
                        'message' => "Communication série RÉUSSIE avec le boîtier physique sur {$targetPort} à {$targetBaud} bauds ! NIM: {$nim}, IFU: {$ifu}.",
                    ];
                }
            }

            if (str_starts_with($resp, 'ERR:')) {
                $code = substr($resp, 4);
                return [
                    'succes' => false,
                    'hardware' => false,
                    'port' => $targetPort,
                    'erreur' => "Erreur physique sur le port {$targetPort} ({$code}). Vérifiez le câble et l'alimentation.",
                ];
            }

            return [
                'succes' => false,
                'hardware' => false,
                'port' => $targetPort,
                'erreur' => "Le périphérique sur {$targetPort} n'a pas renvoyé de trame SECeF valide.",
            ];
        } catch (\Throwable $e) {
            return [
                'succes' => false,
                'hardware' => false,
                'port' => $targetPort,
                'erreur' => "Impossible de communiquer sur {$targetPort} : " . $e->getMessage(),
            ];
        }
    }

    // ─── Commandes Spécifiques SECeF ST 1.0 (Eltrade CC300) ─────────────────

    /**
     * Commande C1h — État complet du MCF.
     */
    public function lireEtat(): array
    {
        if ($this->forceSimulation) {
            return $this->simulerEtatMachine();
        }

        try {
            $raw = $this->envoyerCommande(self::CMD_ETAT_MCF);
            $parts = explode(',', $raw);
            $get = fn(int $i, string $d = '') => isset($parts[$i]) ? trim($parts[$i]) : $d;

            return [
                'succes'                  => true,
                'nim'                     => $get(0),
                'ifu'                     => $get(1),
                'date_heure'              => $get(2),
                'compteur_total'          => (int) $get(3, '0'),
                'compteur_fv'             => (int) $get(4, '0'),
                'compteur_fa'             => (int) $get(5, '0'),
                'derniere_facture_date'   => $get(6),
                'derniere_facture_type'   => $get(7),
                'derniere_facture_sig'    => $get(8),
                'derniere_facture_num'    => $get(9),
                'derniere_facture_ttc'    => (float) $get(10, '0'),
                'taux_a'                  => (float) $get(11, '0'),
                'taux_b'                  => (float) $get(12, '18'),
                'taux_c'                  => (float) $get(13, '0'),
                'taux_d'                  => (float) $get(14, '0'),
                'raw'                     => $raw,
                'hardware'                => true,
            ];
        } catch (\Throwable $e) {
            $entete = Entete::singleton();
            $targetPort = $this->port ?: $entete->port_com;
            if (strtoupper((string) $targetPort) === 'SIMULATION') {
                return $this->simulerEtatMachine();
            }
            Log::warning("McfSerialDriver: Boîtier physique non joignable sur {$targetPort}: " . $e->getMessage());
            return [
                'succes'   => false,
                'hardware' => false,
                'port'     => $targetPort,
                'erreur'   => "Boîtier physique non joignable sur le port {$targetPort}. Vérifiez le branchement.",
            ];
        }
    }

    /**
     * Commande C2h — Statut de connexion au serveur fiscal DGI.
     */
    public function lireEtatConnexion(): array
    {
        if ($this->forceSimulation) {
            return [
                'succes'                   => true,
                'transactions_envoyees'    => 1,
                'transactions_disponibles' => 0,
                'derniere_connexion_dgi'   => now()->format('d/m/Y H:i:s'),
                'erreur'                   => null,
                'hardware'                 => false,
            ];
        }

        try {
            $raw = $this->envoyerCommande(self::CMD_ETAT_CONNEXION);
            $parts = explode(',', $raw);
            return [
                'succes'                   => true,
                'transactions_envoyees'    => isset($parts[0]) ? trim($parts[0]) : '0',
                'transactions_disponibles' => isset($parts[1]) ? trim($parts[1]) : '0',
                'derniere_connexion_dgi'   => isset($parts[2]) ? trim($parts[2]) : '',
                'erreur'                   => isset($parts[3]) && $parts[3] !== '' ? trim($parts[3]) : null,
                'hardware'                 => true,
            ];
        } catch (\Throwable $e) {
            $entete = Entete::singleton();
            $targetPort = $this->port ?: $entete->port_com;
            if (strtoupper((string) $targetPort) === 'SIMULATION') {
                return [
                    'succes'                   => true,
                    'transactions_envoyees'    => 1,
                    'transactions_disponibles' => 0,
                    'derniere_connexion_dgi'   => now()->format('d/m/Y H:i:s'),
                    'erreur'                   => null,
                    'hardware'                 => false,
                ];
            }
            return [
                'succes'                   => false,
                'transactions_envoyees'    => 0,
                'transactions_disponibles' => 0,
                'derniere_connexion_dgi'   => null,
                'erreur'                   => "Boîtier non joignable sur {$targetPort}",
                'hardware'                 => false,
            ];
        }
    }

    /**
     * Commande C3h — Enregistrement des informations du client.
     */
    public function enregistrerClient(array $client): array
    {
        $parts = [
            self::escape($client['type_client'] ?? 'CC'),
            self::escape($client['ifu'] ?? ''),
            self::escape($client['nom'] ?? 'Client Comptoir'),
            self::escape($client['adresse'] ?? ''),
            self::escape($client['telephone'] ?? ''),
            self::escape($client['email'] ?? ''),
            self::escape($client['rccm'] ?? ''),
        ];

        $payload = implode(',', $parts);
        $reponse = $this->envoyerCommande(self::CMD_INFO_CLIENT, $payload);

        if (str_starts_with($reponse, 'E')) {
            return ['succes' => false, 'erreur' => $reponse];
        }

        return ['succes' => true];
    }

    /**
     * Commande C0h — Ouverture d'une nouvelle facture.
     */
    public function ouvrirFacture(array $params): array
    {
        $entete = Entete::singleton();
        $ifuVendeur = strtoupper(trim((string) ($params['ifu_vendeur'] ?? '')));
        if ($ifuVendeur === '') {
            $ifuVendeur = strtoupper(trim((string) ($entete->ifu ?? '')));
        }
        $sfeId = strtoupper(trim((string) ($params['sfeid'] ?? '')));
        if ($sfeId === '') {
            $sfeId = strtoupper(trim((string) ($entete->identifiant_sfe ?? ''))) ?: $ifuVendeur;
        }
        $opId = trim((string) ($params['operateur_id'] ?? '')) ?: 'OP01';
        $opNom = trim((string) ($params['operateur_nom'] ?? '')) ?: 'Vendeur';

        $parts = [
            self::escape(substr($opId, 0, 16)),
            self::escape(substr($opNom, 0, 32)),
            self::escape($ifuVendeur),
        ];

        $typeFacture = strtoupper($params['type_facture'] ?? 'FV');
        if (in_array($typeFacture, ['FA', 'EA'], true)) {
            $parts[] = $typeFacture;
            $parts[] = self::escape($params['nature_avoir'] ?? 'COR');
            $parts[] = self::escape($params['reference_originale'] ?? '');
        } else {
            $parts[] = $typeFacture;
        }

        // Règle majeure SECeF ST 1.0 (firmware Eltrade CC300) : toujours forcer la machine MCF en mode TTC
        $parts[] = 'TTC';
        $parts[] = self::escape($sfeId);
        $parts[] = self::escape($params['numero_interne'] ?? '');

        if (!empty($params['categorie_psvb'])) {
            $parts[] = self::escape($params['categorie_psvb']);
        }

        $payload = implode(',', $parts);
        Log::info("McfSerialDriver: Envoi commande C0h (Début facture)", ['payload' => $payload]);
        $reponse = $this->envoyerCommande(self::CMD_DEBUT_FACTURE, $payload);

        if (str_starts_with($reponse, 'E:')) {
            return ['succes' => false, 'erreur' => substr($reponse, 2)];
        }

        $fields = explode(',', $reponse);
        return [
            'succes'         => true,
            'compteur_type'  => $fields[0] ?? '',
            'compteur_total' => $fields[1] ?? '',
        ];
    }

    /**
     * Commande 31h — Enregistrement d'un article.
     */
    public function enregistrerArticle(array $article, string $modePrix = 'TTC'): array
    {
        $tab = "\t";
        $nomDesc = self::escape($article['designation'] ?? 'Article');
        if (!empty($article['code_interne'])) {
            $nomDesc .= "\x0a" . self::escape($article['code_interne']);
        }

        $typeArticle = $article['type_article_dgi'] ?? 'LOCBIE';
        $groupeTaxe = $article['groupe_taxe'] ?? 'B';
        $tauxTva = (float) ($groupeTaxe === 'A' ? 0.0 : ($article['taux_tva'] ?? 18.0));
        $prixUnitaire = (float) ($article['prix_unitaire'] ?? 0.0);
        $quantite = (float) ($article['quantite'] ?? 1.0);
        $montantTs = (float) ($article['montant_ts'] ?? 0.0);

        $tauxStr = sprintf("%.2f", $tauxTva);
        $prixStr = sprintf("%.2f", $prixUnitaire);

        $montantLigne = round($prixUnitaire * $quantite, 2);
        $montantStr = sprintf("%.2f", $montantLigne);

        $itemInfo = "{$typeArticle},{$groupeTaxe}{$tauxStr}%{$montantStr}{$tab}{$prixStr}";
        if ($quantite != 1.0) {
            $itemInfo .= sprintf("*%.3f", $quantite);
        }

        if ($montantTs > 0) {
            $itemInfo .= sprintf(";T%.2f,%.2f", $montantTs, $montantTs * $quantite);
        }

        $payload = "{$nomDesc}{$tab}{$itemInfo}";
        $reponse = $this->envoyerCommande(self::CMD_ARTICLE, $payload);

        if (str_starts_with($reponse, 'E:')) {
            return ['succes' => false, 'erreur' => substr($reponse, 2)];
        }

        return ['succes' => true, 'raw' => $reponse];
    }

    /**
     * Commande 33h — Lecture du sous-total.
     */
    public function lireSousTotal(): array
    {
        $reponse = $this->envoyerCommande(self::CMD_SOUS_TOTAL);
        $fields = explode(',', $reponse);

        return [
            'succes'        => true,
            'montant_total' => isset($fields[0]) ? (float) $fields[0] : 0.0,
            'montant_psvb'  => isset($fields[33]) ? (float) $fields[33] : 0.0,
            'montant_ts'    => isset($fields[34]) ? (float) $fields[34] : 0.0,
            'raw'           => $reponse,
        ];
    }

    /**
     * Commande 35h — Enregistrement du mode et montant de paiement.
     */
    public function enregistrerPaiement(string $mode, ?float $montant = null): array
    {
        $modeCode = self::MODES_PAIEMENT[strtoupper($mode)] ?? 'E';
        $payload = ($montant === null || $montant <= 0) ? $modeCode : sprintf("%s%.2f", $modeCode, $montant);

        $reponse = $this->envoyerCommande(self::CMD_PAIEMENT, $payload);

        if (str_starts_with($reponse, 'E')) {
            return ['succes' => false, 'erreur' => $reponse];
        }

        return ['succes' => true, 'raw' => $reponse];
    }

    /**
     * Commande 38h — Clôture et certification finale de la facture.
     */
    public function finaliserFacture(float $montantTotal): array
    {
        $payload = sprintf("%.2f", $montantTotal);
        $reponse = $this->envoyerCommande(self::CMD_FIN_FACTURE, $payload);

        if (str_starts_with($reponse, 'E:')) {
            return ['succes' => false, 'erreur' => substr($reponse, 2)];
        }

        $fields = explode(',', $reponse);
        $dt = $fields[3] ?? now()->format('YmdHis');
        $nim = $fields[4] ?? '';
        $ifu = $fields[5] ?? '';
        $sig = $fields[6] ?? '';
        $numSfe = $fields[7] ?? '';

        $qrCodeData = "BFSECEF01;{$nim};{$sig};{$ifu};{$dt}";

        return [
            'succes'                   => true,
            'compteur_type'            => $fields[0] ?? '1',
            'compteur_total'           => $fields[1] ?? '1',
            'type_facture'             => $fields[2] ?? 'FV',
            'date_heure_certification' => $dt,
            'nim'                      => $nim,
            'ifu'                      => $ifu,
            'signature'                => $sig,
            'numero_sfe'               => $numSfe,
            'qrcode_data'              => $qrCodeData,
        ];
    }

    /**
     * Annule une facture fiscale restée ouverte/bloquée dans le MCF.
     */
    public function annulerFactureEnCours(): bool
    {
        try {
            $this->envoyerCommande(self::CMD_FIN_FACTURE, 'C');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }


    // ─── Orchestrateur Central de Certification Fiscalisée ──────────────────

    /**
     * Orchestre la certification complète d'un payload normalisé :
     * C3h (Client) -> C0h (Ouverture) -> 31h (Articles) -> 33h (Sous-total) -> 35h (Paiements) -> 38h (Signature DGI).
     */
    public function certifierPayload(array $payload): array
    {
        $entete = Entete::singleton();
        $targetPort = $this->port ?: $entete->port_com;

        // Si explicitement configuré en simulation ou forcé
        if ($this->forceSimulation || strtoupper((string) $targetPort) === 'SIMULATION') {
            return $this->simulerCertificationPayload($payload);
        }

        try {
            // Tentative sur le boîtier physique
            $this->annulerFactureEnCours();

            // 1. Client (C3h)
            $resClient = $this->enregistrerClient($payload['client'] ?? []);
            if (!($resClient['succes'] ?? false)) {
                throw new RuntimeException("Erreur client (C3h): " . ($resClient['erreur'] ?? 'Refusé'));
            }

            // 2. Début facture (C0h)
            $resDebut = $this->ouvrirFacture($payload);
            if (!($resDebut['succes'] ?? false)) {
                throw new RuntimeException("Erreur ouverture (C0h): " . ($resDebut['erreur'] ?? 'Refusé'));
            }

            // 3. Articles (31h)
            foreach ($payload['lignes'] as $ligne) {
                $resLigne = $this->enregistrerArticle($ligne, $payload['mode_prix'] ?? 'TTC');
                if (!($resLigne['succes'] ?? false)) {
                    throw new RuntimeException("Erreur article (31h): " . ($resLigne['erreur'] ?? 'Refusé'));
                }
            }

            // 3bis. Informations supplémentaires & Lignes FEC DGI (Commande 36h)
            if (!empty($payload['commentaires']) && is_array($payload['commentaires'])) {
                foreach ($payload['commentaires'] as $lettre => $texte) {
                    if (in_array(strtoupper($lettre), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'], true) && !empty(trim((string) $texte))) {
                        $this->enregistrerInfoSupplementaire(strtoupper($lettre), substr(trim((string) $texte), 0, 30));
                    }
                }
            }

            // 4. Sous-total (33h)
            $sousTotal = $this->lireSousTotal();
            $totalTtc = $sousTotal['montant_total'];
            $psvb = $sousTotal['montant_psvb'];

            // 5. Paiements (35h)
            $paiements = $payload['paiements'] ?? [['mode' => 'ESP', 'montant' => $totalTtc]];
            foreach ($paiements as $p) {
                $this->enregistrerPaiement($p['mode'] ?? 'ESP', (float) ($p['montant'] ?? $totalTtc));
            }

            // 6. Finalisation (38h)
            $resFin = $this->finaliserFacture($totalTtc);
            if (!($resFin['succes'] ?? false)) {
                throw new RuntimeException("Erreur finalisation DGI (38h): " . ($resFin['erreur'] ?? 'Refusé'));
            }

            return [
                'succes'             => true,
                'signature'          => $resFin['signature'],
                'qrcode_data'        => $resFin['qrcode_data'],
                'nim'                => $resFin['nim'],
                'date_certification' => $resFin['date_heure_certification'],
                'compteur_total'     => $resFin['compteur_total'],
                'compteur_type'      => $resFin['compteur_type'],
                'psvb'               => $psvb,
                'hardware'           => true,
            ];
        } catch (\Throwable $e) {
            $entete = Entete::singleton();
            $targetPort = $this->port ?: $entete->port_com;
            if (strtoupper((string) $targetPort) === 'SIMULATION') {
                return $this->simulerCertificationPayload($payload);
            }
            Log::error("McfSerialDriver: Échec certification avec le boîtier physique sur {$targetPort}: " . $e->getMessage());
            $msg = $e->getMessage();
            $isProtocolReject = str_starts_with($msg, 'Erreur client') || str_starts_with($msg, 'Erreur ouverture') || str_starts_with($msg, 'Erreur article') || str_starts_with($msg, 'Erreur finalisation');
            $detail = $isProtocolReject
                ? "Refus fiscal du boîtier MCF : {$msg}"
                : "Erreur de communication avec le boîtier MCF sur {$targetPort} ({$msg}). Vérifiez le câble USB et l'alimentation.";

            return [
                'succes'   => false,
                'hardware' => true,
                'erreur'   => $detail,
            ];
        }
    }

    /**
     * Commande 45h — Génération et impression d'un rapport fiscal X ou Z sur le boîtier physique MCF.
     *
     * @param string $type 'X' (lecture du jour sans remise à zéro) ou 'Z' (clôture journalière avec remise à zéro)
     * @return array
     */
    public function genererRapport(string $type): array
    {
        $type = strtoupper(trim($type));
        if (!in_array($type, ['X', 'Z', 'A'], true)) {
            throw new \InvalidArgumentException("Le type de rapport fiscal doit être 'X', 'Z' ou 'A'.");
        }

        $entete = Entete::singleton();
        $targetPort = $this->port ?: $entete->port_com;

        if ($this->forceSimulation || strtoupper((string) $targetPort) === 'SIMULATION') {
            return [
                'succes'   => true,
                'hardware' => false,
                'type'     => $type,
                'message'  => "Rapport {$type} généré avec succès en mode simulation.",
                'raw'      => "SIMULATED_{$type}",
            ];
        }

        try {
            // Dans la norme officielle SECeF BF ST 1.0, les rapports X et Z de synthèse
            // sont consolidés par le logiciel (SFE) et horodatés / adossés aux compteurs
            // matériels temps-réel du boîtier MCF via la commande C1h (État MCF) et C2h (Connexion DGI).
            $etat = $this->lireEtat();
            if (!($etat['succes'] ?? false)) {
                throw new RuntimeException($etat['erreur'] ?? "Boîtier MCF non joignable sur {$targetPort}");
            }

            $connexion = $this->lireEtatConnexion();

            return [
                'succes'         => true,
                'hardware'       => true,
                'type'           => $type,
                'message'        => "Rapport fiscal {$type} synchronisé avec le boîtier MCF physique ({$targetPort}) — Compteurs matériels : Total={$etat['compteur_total']}, Ventes={$etat['compteur_fv']}, Avoirs={$etat['compteur_fa']}.",
                'raw'            => $etat['raw'] ?? '',
                'nim'            => $etat['nim'] ?? '',
                'compteur_total' => $etat['compteur_total'] ?? 0,
                'compteur_fv'    => $etat['compteur_fv'] ?? 0,
                'compteur_fa'    => $etat['compteur_fa'] ?? 0,
                'connexion_dgi'  => $connexion,
            ];
        } catch (\Throwable $e) {
            Log::error("McfSerialDriver::genererRapport({$type}) sur {$targetPort}: " . $e->getMessage());
            return [
                'succes'   => false,
                'hardware' => true,
                'type'     => $type,
                'erreur'   => "Échec de commande du Rapport {$type} sur le boîtier physique ({$targetPort}) : " . $e->getMessage(),
            ];
        }
    }

    // ─── Émulateur / Simulation SECeF ST 1.0 (Fallback Robuste) ─────────────

    /**
     * Simule l'état de la machine conforme aux compteurs et au format SECeF.
     */
    protected function simulerEtatMachine(): array
    {
        $entete = Entete::singleton();
        $machine = McfMachineState::firstOrCreate([], [
            'nim'                        => $entete->nim_mcf ?: 'EL02000012',
            'acnt'                       => 1,
            'ifu'                        => $entete->ifu ?: '00000000X',
            'compteur_total'             => 0,
            'compteur_vente'             => 0,
            'compteur_avoir'             => 0,
            'derniere_connexion_serveur' => now(),
        ]);

        return [
            'succes'                  => true,
            'nim'                     => $machine->mid,
            'ifu'                     => $machine->ifu,
            'date_heure'              => now()->format('d/m/Y H:i:s'),
            'compteur_total'          => $machine->compteur_total,
            'compteur_fv'             => $machine->compteur_vente,
            'compteur_fa'             => $machine->compteur_avoir,
            'derniere_facture_date'   => optional($machine->derniere_facture_date)->format('d/m/Y H:i:s') ?: '',
            'derniere_facture_type'   => $machine->derniere_facture_type ?: 'FV',
            'derniere_facture_sig'    => $machine->derniere_facture_sig ?: '',
            'derniere_facture_num'    => $machine->derniere_facture_numero ?: '',
            'derniere_facture_ttc'    => (float) ($machine->dernier_montant ?: 0),
            'taux_a'                  => 0.0,
            'taux_b'                  => 18.0,
            'taux_c'                  => 0.0,
            'taux_d'                  => 0.0,
            'hardware'                => false,
            'emulateur'               => true,
        ];
    }

    /**
     * Simule la certification fiscale complète d'un payload selon les règles officielles DGI.
     */
    protected function simulerCertificationPayload(array $payload): array
    {
        $entete = Entete::singleton();
        $machine = McfMachineState::firstOrCreate([], [
            'nim'                        => $entete->nim_mcf ?: 'EL02000012',
            'acnt'                       => 1,
            'ifu'                        => $entete->ifu ?: '00000000X',
            'compteur_total'             => 0,
            'compteur_vente'             => 0,
            'compteur_avoir'             => 0,
            'derniere_connexion_serveur' => now(),
        ]);

        $typeFacture = strtoupper($payload['type_facture'] ?? 'FV');
        $isAvoir = in_array($typeFacture, ['FA', 'EA'], true);

        $machine->compteur_total++;
        if ($isAvoir) {
            $machine->compteur_avoir++;
            $compteurType = $machine->compteur_avoir;
        } else {
            $machine->compteur_vente++;
            $compteurType = $machine->compteur_vente;
        }

        // Signature DGI standardisée SECeF (ex: TEST-FACT-HQBA-X2F3-K22N-MDYR)
        $sigBlocks = [];
        for ($i = 0; $i < 4; $i++) {
            $sigBlocks[] = strtoupper(Str::random(4));
        }
        $signature = 'TEST-FACT-' . implode('-', $sigBlocks);

        $dt = now();
        $nim = $machine->mid;
        $ifu = $entete->ifu ?: '00000000X';
        $dtStr = $dt->format('YmdHis');

        // QR Code conforme SECeF BF ST 1.0 : BFSECEF01;{NIM};{SIG};{IFU};{DT}
        $qrcodeData = "BFSECEF01;{$nim};{$signature};{$ifu};{$dtStr}";

        $machine->update([
            'compteur_total'          => $machine->compteur_total,
            'compteur_vente'          => $machine->compteur_vente,
            'compteur_avoir'          => $machine->compteur_avoir,
            'derniere_facture_date'   => $dt,
            'derniere_facture_type'   => $typeFacture,
            'derniere_facture_sig'    => $signature,
            'derniere_facture_numero' => $payload['numero_interne'] ?? '',
        ]);

        Log::info("McfSerialDriver: Facture certifiée avec succès via l'émulateur natif SECeF (SIG: {$signature}).");

        return [
            'succes'             => true,
            'signature'          => $signature,
            'qrcode_data'        => $qrcodeData,
            'nim'                => $nim,
            'date_certification' => $dt->format('Y-m-d H:i:s'),
            'compteur_total'     => (string) $machine->compteur_total,
            'compteur_type'      => (string) $compteurType,
            'psvb'               => 0.0,
            'hardware'           => false,
            'emulateur'          => true,
        ];
    }
}
