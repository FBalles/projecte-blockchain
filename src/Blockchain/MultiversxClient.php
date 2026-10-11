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
            error_log("MX Error: PEM file not found at $pemPath");
            return null;
        }

        // Set HOME for www-data and include local binary paths in PATH
        $envVars = 'HOME=/tmp PATH=$PATH:/home/debian/.local/bin:/usr/local/bin:/usr/bin';

        $cmd = sprintf(
            '%s mxpy contract call %s '
            . '--pem %s '
            . '--proxy %s '
            . '--function recordEvent '
            . '--arguments %d %d str:%s %d '
            . '--gas-limit 50000000 '
            . '--send --wait-result 2>&1',
            $envVars,
            escapeshellarg($contract),
            escapeshellarg($pemPath),
            escapeshellarg($gateway),
            $serviceId,
            $companyId,
            escapeshellarg($eventType),
            time()
        );

        exec($cmd, $output, $returnCode);
        $fullOutput = implode("\n", $output);

        if ($returnCode !== 0) {
            error_log("MX Error exec (code $returnCode): " . $fullOutput);
            return null;
        }

        // 1. Try direct JSON parsing
        $json = json_decode($fullOutput, true);
        if (is_array($json)) {
            if (!empty($json['emittedTransactionHash'])) {
                return $json['emittedTransactionHash'];
            }
            if (!empty($json['transactionOnNetwork']['hash'])) {
                return $json['transactionOnNetwork']['hash'];
            }
            if (!empty($json['emittedTransaction']['hash'])) {
                return $json['emittedTransaction']['hash'];
            }
        }

        // 2. Fallback regex matching for JSON keys or log lines
        if (preg_match('/"(?:emittedTransactionHash|hash)":\s*"([0-9a-fA-F]{64})"/', $fullOutput, $m)) {
            return $m[1];
        }

        if (preg_match('/(?:emitted transaction hash|txhash|tx hash):\s*([0-9a-fA-F]{64})/i', $fullOutput, $m)) {
            return $m[1];
        }

        error_log("MX Error: Could not extract txHash from output: " . $fullOutput);
        return null;
    }

    public static function getHistory(): array
    {
        $pemPath  = env('MX_PEM_PATH');
        $contract = env('MX_CONTRACT_ADDRESS');
        $gateway  = env('MX_GATEWAY');

        $address = self::getAddress($pemPath);
        if (!$address) return [];

        $envVars = 'HOME=/tmp PATH=$PATH:/home/debian/.local/bin:/usr/local/bin:/usr/bin';

        $cmd = sprintf(
            '%s mxpy contract query %s --function getHistory --arguments %s --proxy %s 2>&1',
            $envVars,
            escapeshellarg($contract),
            escapeshellarg($address),
            escapeshellarg($gateway)
        );

        exec($cmd, $output, $rc);
        if ($rc !== 0) {
            error_log("MX Error executing query: " . implode("\n", $output));
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
        if (!file_exists($pemPath)) {
            return null;
        }

        $envVars = 'HOME=/tmp PATH=$PATH:/home/debian/.local/bin:/usr/local/bin:/usr/bin';
        $cmd = sprintf('%s mxpy wallet address --pem %s 2>&1', $envVars, escapeshellarg($pemPath));

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