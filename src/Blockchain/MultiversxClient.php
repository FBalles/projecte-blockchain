<?php
declare(strict_types=1);

class MultiversxClient
{
    public static function send(int $serviceId, int $companyId, string $eventType): ?string
    {
        $pemPath   = env('MX_PEM_PATH');
        $contract  = env('MX_CONTRACT_ADDRESS');
        $gateway   = env('MX_GATEWAY');

        if (!file_exists($pemPath)) {
            error_log("MX: PEM no encontrado en $pemPath");
            return null;
        }

        $data = sprintf(
            'recordEvent@%s@%s@%s@%s',
            dechex($serviceId),
            dechex($companyId),
            $eventType,
            dechex(time())
        );

        $cmd = sprintf(
            'mxpy contract call %s '
            . '--pem %s '
            . '--proxy %s '
            . '--function recordEvent '
            . '--arguments %s %s %s %s '
            . '--gas-limit 50000000 '
            . '--send --wait-result 2>&1',
            $contract,
            escapeshellarg($pemPath),
            $gateway,
            dechex($serviceId),
            dechex($companyId),
            $eventType,
            dechex(time())
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            error_log("MX: Error mxpy: " . implode("\n", $output));
            return null;
        }

        // Parsear txHash del output de mxpy
        foreach ($output as $line) {
            if (preg_match('/txHash:\s*(\w+)/', $line, $m)) {
                return $m[1];
            }
            if (preg_match('/tx hash:\s*(\w+)/i', $line, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    public static function getHistory(): array
    {
        $pemPath  = env('MX_PEM_PATH');
        $contract = env('MX_CONTRACT_ADDRESS');
        $gateway  = env('MX_GATEWAY');

        // Obtener dirección de la wallet
        $address = self::getAddress($pemPath);
        if (!$address) return [];

        // Query al contrato
        $ch = curl_init($gateway . '/smart-contract/query');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'scAddress' => $contract,
                'funcName'  => 'getHistory',
                'args'      => [$address],
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $raw = $res['data']['returnData'][0] ?? '';
        $decoded = base64_decode($raw);

        // El returnData viene como array de ManagedBuffer (un hex por evento)
        $events = [];
        if (preg_match_all('/"([0-9a-f]+)"/', $decoded, $matches)) {
            foreach ($matches[1] as $hex) {
                $events[] = hex2bin($hex);
            }
        }

        return $events;
    }

    private static function getAddress(string $pemPath): ?string
    {
        $cmd = sprintf('mxpy wallet address --pem %s 2>&1', escapeshellarg($pemPath));
        exec($cmd, $output, $rc);
        if ($rc === 0) {
            foreach ($output as $line) {
                if (str_starts_with(trim($line), 'erd1')) {
                    return trim($line);
                }
            }
        }
        return null;
    }
}