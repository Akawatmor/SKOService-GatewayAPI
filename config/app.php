<?php

declare(strict_types=1);

return [
    'name' => 'GatewayAPI',
    'env' => getenv('APP_ENV') ?: 'development',
    'debug' => (getenv('APP_DEBUG') ?: '1') === '1',
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Bangkok',
    'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost:8000',
    'lang' => ['default' => 'th', 'fallback' => 'en'],
];