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
            file_put_contents('/tmp/mx_debug.log', "[" . date('Y-m-d H:i:s') . "] Query Error: Could not resolve address from '$pemPath'\n", FILE_APPEND);
            return [];
        }

        // Added addr: prefix for address arguments
        $cmd = sprintf(
            'HOME=/tmp %s contract query %s --function getHistory --arguments addr:%s --proxy %s 2>&1',
            escapeshellarg(self::MXPY_BIN),
            escapeshellarg($contract),
            escapeshellarg($address),
            escapeshellarg($gateway)
        );

        exec($cmd, $output, $rc);
        $fullOutput = implode("\n", $output);

        $logData = "[" . date('Y-m-d H:i:s') . "] Query Executed ($address): $cmd\nReturn Code: $rc\nOutput:\n$fullOutput\n----------------------------------------\n";
        file_put_contents('/tmp/mx_debug.log', $logData, FILE_APPEND);

        if ($rc !== 0) {
            return [];
        }

        $events = [];
        $json = json_decode($fullOutput, true);

        if (is_array($json)) {
            foreach ($json as $item) {
                $hex = is_array($item) ? ($item['hex'] ?? null) : $item;
                if ($hex && is_string($hex)) {
                    $rawBinary = @hex2bin($hex);
                    if ($rawBinary !== false) {
                        // Unpack MultiversX ManagedVec length-prefixed items
                        $offset = 0;
                        $totalLen = strlen($rawBinary);
                        
                        while ($offset + 4 <= $totalLen) {
                            $itemLen = unpack('N', substr($rawBinary, $offset, 4))[1];
                            $offset += 4;
                            if ($offset + $itemLen > $totalLen) break;
                            $events[] = substr($rawBinary, $offset, $itemLen);
                            $offset += $itemLen;
                        }

                        // Fallback if no binary length headers were present
                        if (empty($events) && ctype_print($rawBinary)) {
                            $events[] = $rawBinary;
                        }
                    }
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

        $content = file_get_contents($pemPath);
        if ($content && preg_match('/(erd1[0-9a-z]{58})/i', $content, $m)) {
            return $m[1];
        }

        return null;
    }
}