<?php

declare(strict_types=1);

namespace GatewayAPI\Services;

use GatewayAPI\Core\App;

final class ApiKeyService
{
    /**
     * @return array<string, string>
     */
    public static function generate(): array
    {
        $plain = 'sk_live_' . bin2hex(random_bytes(24));
        $prefixLength = (int) App::config('auth.api_key_prefix_length', 4);

        return [
            'plain' => $plain,
            'hash' => hash('sha256', $plain),
            'prefix' => substr($plain, 0, max($prefixLength + 8, 12)),
        ];
    }
}