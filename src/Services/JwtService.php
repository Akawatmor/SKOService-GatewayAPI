<?php

declare(strict_types=1);

namespace GatewayAPI\Services;

use GatewayAPI\Core\App;

final class JwtService
{
    public static function encode(array $payload, int $ttl): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $issuedAt = time();
        $payload = [...$payload, 'iat' => $issuedAt, 'exp' => $issuedAt + $ttl];

        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES) ?: '{}'),
            self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}'),
        ];
        $signature = hash_hmac('sha256', implode('.', $segments), (string) App::config('auth.jwt_secret'), true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function decode(?string $token): ?array
    {
        if ($token === null || $token === '') {
            return null;
        }

        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $segments;
        $signature = self::base64UrlDecode($encodedSignature);
        $expected = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, (string) App::config('auth.jwt_secret'), true);

        if ($signature === null || !hash_equals($expected, $signature)) {
            return null;
        }

        $payload = json_decode((string) self::base64UrlDecode($encodedPayload), true);

        if (!is_array($payload) || (($payload['exp'] ?? 0) < time())) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string|null
    {
        $remainder = strlen($value) % 4;

        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}