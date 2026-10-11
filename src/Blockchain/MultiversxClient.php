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

        $logData = "[" . date('Y-m-d H:i:s') . "] Send Executed: $cmd\nReturn Code: $returnCode\nOutput:\n$fullOutput\n----------------------------------------\n";
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

        return null;
    }

    public static function getHistory(): array
    {
        $pemPath  = env('MX_PEM_PATH');
        $contract = env('MX_CONTRACT_ADDRESS');
        $gateway  = env('MX_GATEWAY');

        $address = self::getAddress($pemPath);
        if (!$address) {
            file_put_contents('/tmp/mx_debug.log', "[" . date('Y-m-d H:i:s') . "] Query Error: Could not resolve wallet address from '$pemPath'\n", FILE_APPEND);
            return [];
        }

        $cmd = sprintf(
            'HOME=/tmp %s contract query %s --function getHistory --arguments %s --proxy %s 2>&1',
            escapeshellarg(self::MXPY_BIN),
            escapeshellarg($contract),
            escapeshellarg($address),
            escapeshellarg($gateway)
        );

        exec($cmd, $output, $rc);
        $fullOutput = implode("\n", $output);

        $logData = "[" . date('Y-m-d H:i:s') . "] Query Executed: $cmd\nReturn Code: $rc\nOutput:\n$fullOutput\n----------------------------------------\n";
        file_put_contents('/tmp/mx_debug.log', $logData, FILE_APPEND);

        if ($rc !== 0) {
            return [];
        }

        $events = [];

        // Parse JSON output from mxpy contract query
        $json = json_decode($fullOutput, true);
        if (is_array($json)) {
            foreach ($json as $item) {
                if (is_array($item)) {
                    if (!empty($item['hex'])) {
                        $decoded = @hex2bin($item['hex']);
                        if ($decoded !== false) $events[] = $decoded;
                    } elseif (!empty($item['base64'])) {
                        $decoded = @base64_decode($item['base64']);
                        if ($decoded !== false) $events[] = $decoded;
                    }
                } elseif (is_string($item)) {
                    $decodedHex = @hex2bin($item);
                    if ($decodedHex !== false && ctype_print($decodedHex)) {
                        $events[] = $decodedHex;
                    } else {
                        $decodedB64 = @base64_decode($item);
                        if ($decodedB64 !== false) $events[] = $decodedB64;
                    }
                }
            }

            if (!empty($events)) {
                return $events;
            }
        }

        // Fallback regex for hex values
        if (preg_match_all('/"(?:hex|base64)":\s*"([^"]+)"/', $fullOutput, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $val = $m[1];
                if (ctype_xdigit($val) && strlen($val) % 2 === 0) {
                    $decoded = @hex2bin($val);
                    if ($decoded !== false) $events[] = $decoded;
                } else {
                    $decoded = @base64_decode($val);
                    if ($decoded !== false) $events[] = $decoded;
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
        $fullOutput = implode("\n", $output);

        if ($rc === 0 && preg_match('/(erd1[0-9a-z]{58})/i', $fullOutput, $m)) {
            return $m[1];
        }

        file_put_contents('/tmp/mx_debug.log', "[" . date('Y-m-d H:i:s') . "] getAddress Failed (rc=$rc). Output:\n$fullOutput\n----------------------------------------\n", FILE_APPEND);
        return null;
    }
}