<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

use PDO;

final class AccessGrant extends BaseModel
{
    protected static string $table = 'access_grants';

    public static function hasGrant(int $userId, int $serviceId): bool
    {
        $statement = static::pdo()->prepare(
            'SELECT COUNT(*)
             FROM access_grants
             WHERE user_id = :user_id AND api_service_id = :api_service_id
             AND (expires_at IS NULL OR expires_at >= datetime("now"))'
        );
        $statement->execute([
            'user_id' => $userId,
            'api_service_id' => $serviceId,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function forUser(int $userId): array
    {
        $statement = static::pdo()->prepare(
            'SELECT access_grants.*, api_services.name AS api_name, api_services.slug
             FROM access_grants
             INNER JOIN api_services ON api_services.id = access_grants.api_service_id
             WHERE access_grants.user_id = :user_id
             ORDER BY access_grants.granted_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }
}