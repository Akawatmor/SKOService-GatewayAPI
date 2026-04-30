<?php

declare(strict_types=1);

return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'change-this-secret-before-production',
    'jwt_ttl' => 3600,
    'refresh_ttl' => 604800,
    'api_key_prefix_length' => 4,
    'bcrypt_cost' => 12,
];