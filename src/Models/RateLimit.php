<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

final class RateLimit extends BaseModel
{
    protected static string $table = 'rate_limits';

    /**
     * @return array<string, mixed>
     */
    public static function resolve(?int $serviceId, ?int $roleId): array
    {
        $statement = static::pdo()->prepare(
            'SELECT *
             FROM rate_limits
             WHERE (api_service_id = :service_id OR api_service_id IS NULL)
             AND (role_id = :role_id OR role_id IS NULL)
             ORDER BY api_service_id IS NOT NULL DESC, role_id IS NOT NULL DESC
             LIMIT 1'
        );
        $statement->execute([
            'service_id' => $serviceId,
            'role_id' => $roleId,
        ]);
        $result = $statement->fetch();

        return is_array($result)
            ? $result
            : ['requests_per_minute' => 60, 'requests_per_day' => 1000];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminList(): array
    {
        $statement = static::pdo()->query(
            'SELECT rate_limits.*, api_services.name AS api_name, roles.name AS role_name
             FROM rate_limits
             LEFT JOIN api_services ON api_services.id = rate_limits.api_service_id
             LEFT JOIN roles ON roles.id = rate_limits.role_id
             ORDER BY api_name IS NULL DESC, api_name ASC, role_name ASC'
        );

        return $statement !== false ? $statement->fetchAll() : [];
    }
}