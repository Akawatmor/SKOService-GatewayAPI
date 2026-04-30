<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

final class Role extends BaseModel
{
    protected static string $table = 'roles';

    /**
     * @return array<string, mixed>|null
     */
    public static function findByName(string $name): ?array
    {
        return static::firstWhere(['name' => $name]);
    }
}