<?php
declare(strict_types=1);

class MultiversxClient
{
    private const MXPY_BIN = '/usr/local/bin/mxpy';

    public static function send(int $serviceId, int $companyId, string $eventType): ?string
    {
        $pemPath  = env('MX_PEM_PATH');
        $contract = env('MX_CONTRACT_ADDRESS');
        $gateway  = env('MX_GATEWAY');

        if (!$pemPath || !file_exists($pemPath)) {
            file_put_contents('/tmp/mx_debug.log', "[" . date('Y-m-d H:i:s') . "] MX Error: PEM file not found at '$pemPath'\n", FILE_APPEND);
            return null;
        }

        $timestamp = time();

        $cmd = sprintf(
            'HOME=/tmp %s contract call %s '
            . '--pem %s '
            . '--proxy %s '
            . '--function recordEvent '
            . '--arguments %d %d str:%s %d '
            . '--gas-limit 50000000 '
            . '--send --wait-result 2>&1',
            escapeshellarg(self::MXPY_BIN),
            escapeshellarg($contract),
            escapeshellarg($pemPath),
            escapeshellarg($gateway),
            $serviceId,
            $companyId,
            escapeshellcmd($eventType),
            $timestamp
        );

        exec($cmd, $output, $returnCode);
        $fullOutput = implode("\n", $output);

        // Debug log write
        $logData = "[" . date('Y-m-d H:i:s') . "] Executed: $cmd\nReturn Code: $returnCode\nOutput:\n$fullOutput\n----------------------------------------\n";
        file_put_contents('/tmp/mx_debug.log', $logData, FILE_APPEND);

        if ($returnCode !== 0) {
            return null;
        }

        // 1. JSON parsing
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

        // 2. Regex fallback
        if (preg_match('/"(?:emittedTransactionHash|hash)":\s*"([0-9a-fA-F]{64})"/', $fullOutput, $m)) {
            return $m[1];
        }

        if (preg_match('/(?:emitted transaction hash|txhash|tx hash):\s*([0-9a-fA-F]{64})/i', $fullOutput, $m)) {
            return $m[1];
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

        $cmd = sprintf(
            'HOME=/tmp %s contract query %s --function getHistory --arguments %s --proxy %s 2>&1',
            escapeshellarg(self::MXPY_BIN),
            escapeshellarg($contract),
            escapeshellarg($address),
            escapeshellarg($gateway)
        );

        exec($cmd, $output, $rc);

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

    private static function getAddress(?string $pemPath): ?string
    {
        if (!$pemPath || !file_exists($pemPath)) {
            return null;
        }

        $cmd = sprintf('HOME=/tmp %s wallet address --pem %s 2>&1', escapeshellarg(self::MXPY_BIN), escapeshellarg($pemPath));

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