<?php

declare(strict_types=1);

namespace GatewayAPI\Services;

use RuntimeException;

final class ProxyService
{
    /**
     * @param array<string, mixed> $service
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     * @return array{status:int, headers:array<string, string>, body:string, response_time_ms:int}
     */
    public static function forward(array $service, string $path, string $method, array $headers = [], array $query = [], ?string $body = null): array
    {
        $baseUrl = rtrim((string) ($service['base_url'] ?? ''), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Service base URL is not configured.');
        }

        $path = '/' . ltrim($path, '/');
        $url = $baseUrl . ($service['api_type'] === 'GraphQL' ? '' : $path);

        if ($query !== []) {
            $queryString = http_build_query($query);
            if ($queryString !== '') {
                $url .= (str_contains($url, '?') ? '&' : '?') . $queryString;
            }
        }

        if (function_exists('curl_init')) {
            return self::forwardWithCurl($url, $method, $headers, $body);
        }

        if (self::curlCommandAvailable()) {
            return self::forwardWithCliCurl($url, $method, $headers, $body);
        }

        return self::forwardWithStream($url, $method, $headers, $body);
    }

    /**
     * @param array<string, string> $headers
     * @return array{status:int, headers:array<string, string>, body:string, response_time_ms:int}
     */
    private static function forwardWithCurl(string $url, string $method, array $headers, ?string $body): array
    {
        $ch = curl_init($url);

        if ($ch === false) {
            throw new RuntimeException('Unable to initialize cURL.');
        }

        $responseHeaders = [];
        $normalizedHeaders = [];

        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), ['host', 'content-length'], true)) {
                continue;
            }

            $normalizedHeaders[] = $name . ': ' . $value;
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $normalizedHeaders,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $headerLine) use (&$responseHeaders): int {
                $length = strlen($headerLine);
                $parts = explode(':', $headerLine, 2);

                if (count($parts) === 2) {
                    $responseHeaders[trim($parts[0])] = trim($parts[1]);
                }

                return $length;
            },
        ]);

        if ($body !== null && $body !== '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $startedAt = microtime(true);
        $bodyResponse = curl_exec($ch);
        $duration = (int) round((microtime(true) - $startedAt) * 1000);

        if ($bodyResponse === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException($message === '' ? 'Proxy request failed.' : $message);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => $bodyResponse,
            'response_time_ms' => $duration,
        ];
    }

    /**
     * @param array<string, string> $headers
     * @return array{status:int, headers:array<string, string>, body:string, response_time_ms:int}
     */
    private static function forwardWithStream(string $url, string $method, array $headers, ?string $body): array
    {
        $headerLines = [];

        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), ['host', 'content-length'], true)) {
                continue;
            }

            $headerLines[] = $name . ': ' . $value;
        }

        $context = stream_context_create([
            'http' => [
                'method' => strtoupper($method),
                'ignore_errors' => true,
                'header' => implode("\r\n", $headerLines),
                'content' => $body ?? '',
                'timeout' => 20,
            ],
        ]);

        $startedAt = microtime(true);
        $bodyResponse = @file_get_contents($url, false, $context);
        $duration = (int) round((microtime(true) - $startedAt) * 1000);

        if ($bodyResponse === false) {
            throw new RuntimeException('Proxy request failed.');
        }

        $responseHeaders = [];
        $status = 200;

        foreach ($http_response_header ?? [] as $line) {
            if (str_starts_with($line, 'HTTP/')) {
                $parts = explode(' ', $line);
                $status = isset($parts[1]) ? (int) $parts[1] : $status;
                continue;
            }

            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $responseHeaders[trim($parts[0])] = trim($parts[1]);
            }
        }

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => $bodyResponse,
            'response_time_ms' => $duration,
        ];
    }

    private static function curlCommandAvailable(): bool
    {
        $command = PHP_OS_FAMILY === 'Windows' ? 'where curl' : 'command -v curl';
        $output = shell_exec($command . ' 2>&1');

        return is_string($output) && trim($output) !== '';
    }

    /**
     * @param array<string, string> $headers
     * @return array{status:int, headers:array<string, string>, body:string, response_time_ms:int}
     */
    private static function forwardWithCliCurl(string $url, string $method, array $headers, ?string $body): array
    {
        $headerFile = tempnam(sys_get_temp_dir(), 'gatewayapi-headers-');
        $bodyFile = tempnam(sys_get_temp_dir(), 'gatewayapi-body-');

        if ($headerFile === false || $bodyFile === false) {
            throw new RuntimeException('Unable to allocate proxy temp files.');
        }

        $parts = [
            'curl',
            '-sS',
            '-L',
            '-X', escapeshellarg(strtoupper($method)),
            '-D', escapeshellarg($headerFile),
            '-o', escapeshellarg($bodyFile),
        ];

        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), ['host', 'content-length'], true)) {
                continue;
            }

            $parts[] = '-H';
            $parts[] = escapeshellarg($name . ': ' . $value);
        }

        if ($body !== null && $body !== '') {
            $parts[] = '--data-binary';
            $parts[] = escapeshellarg($body);
        }

        $parts[] = escapeshellarg($url);

        $startedAt = microtime(true);
        $output = [];
        $exitCode = 0;
        exec(implode(' ', $parts) . ' 2>&1', $output, $exitCode);
        $duration = (int) round((microtime(true) - $startedAt) * 1000);

        try {
            if ($exitCode !== 0) {
                $message = trim(implode("\n", $output));
                throw new RuntimeException($message !== '' ? $message : 'Proxy request failed.');
            }
            $bodyResponse = file_get_contents($bodyFile);
            $rawHeaders = file($headerFile, FILE_IGNORE_NEW_LINES) ?: [];

            if ($bodyResponse === false) {
                throw new RuntimeException('Unable to read proxied response body.');
            }

            $responseHeaders = [];
            $status = 200;

            foreach ($rawHeaders as $line) {
                if (str_starts_with($line, 'HTTP/')) {
                    $parts = preg_split('/\s+/', trim($line));
                    $status = isset($parts[1]) ? (int) $parts[1] : $status;
                    $responseHeaders = [];
                    continue;
                }

                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $responseHeaders[trim($parts[0])] = trim($parts[1]);
                }
            }

            return [
                'status' => $status,
                'headers' => $responseHeaders,
                'body' => $bodyResponse,
                'response_time_ms' => $duration,
            ];
        } finally {
            @unlink($headerFile);
            @unlink($bodyFile);
        }
    }
}