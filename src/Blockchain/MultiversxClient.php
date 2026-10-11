<?php
declare(strict_types=1);

class MultiversxClient
{
    public static function send(int $serviceId, int $companyId, string $eventType): ?string
    {
        $pemPath  = env('MX_PEM_PATH');
        $contract = env('MX_CONTRACT_ADDRESS');
        $gateway  = env('MX_GATEWAY');

        if (!file_exists($pemPath)) {
            error_log("MX: PEM no encontrado en $pemPath");
            return null;
        }

        // mxpy requires str: prefix for raw string args and decimal/hex format for numbers
        $cmd = sprintf(
            'mxpy contract call %s '
            . '--pem %s '
            . '--proxy %s '
            . '--function recordEvent '
            . '--arguments %d %d str:%s %d '
            . '--gas-limit 50000000 '
            . '--send --wait-result 2>&1',
            escapeshellarg($contract),
            escapeshellarg($pemPath),
            escapeshellarg($gateway),
            $serviceId,
            $companyId,
            escapeshellcmd($eventType),
            time()
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            error_log("MX: Error mxpy: " . implode("\n", $output));
            return null;
        }

        foreach ($output as $line) {
            if (preg_match('/txHash:\s*([0-9a-fA-F]+)/', $line, $m)) {
                return $m[1];
            }
            if (preg_match('/tx hash:\s*([0-9a-fA-F]+)/i', $line, $m)) {
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

        $address = self::getAddress($pemPath);
        if (!$address) return [];

        // Query SC using mxpy (handles Bech32 address conversion automatically)
        $cmd = sprintf(
            'mxpy contract query %s --function getHistory --arguments %s --proxy %s 2>&1',
            escapeshellarg($contract),
            escapeshellarg($address),
            escapeshellarg($gateway)
        );

        exec($cmd, $output, $rc);
        if ($rc !== 0) {
            error_log("MX: Error executing query: " . implode("\n", $output));
            return [];
        }

        $events = [];
        foreach ($output as $line) {
            if (preg_match('/"([0-9a-fA-F]+)"/', $line, $m)) {
                $decoded = @hex2bin($m[1]);
                if ($decoded !== false) {
                    $events[] = $decoded;
                }
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
                $trimmed = trim($line);
                if (str_starts_with($trimmed, 'erd1')) {
                    return $trimmed;
                }
            }
        }
        return null;
    }
}