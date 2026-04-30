<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

use PDO;

final class AccessRequest extends BaseModel
{
    protected static string $table = 'access_requests';

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function forUser(int $userId): array
    {
        $statement = static::pdo()->prepare(
            'SELECT access_requests.*, api_services.name AS api_name
             FROM access_requests
             INNER JOIN api_services ON api_services.id = access_requests.api_service_id
             WHERE access_requests.user_id = :user_id
             ORDER BY access_requests.requested_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function pendingList(): array
    {
        $statement = static::pdo()->query(
            "SELECT access_requests.*, users.email AS user_email, api_services.name AS api_name
             FROM access_requests
             INNER JOIN users ON users.id = access_requests.user_id
             INNER JOIN api_services ON api_services.id = access_requests.api_service_id
             WHERE access_requests.status = 'pending'
             ORDER BY access_requests.requested_at ASC"
        );

        return $statement !== false ? $statement->fetchAll() : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findDetailed(int $id): ?array
    {
        $statement = static::pdo()->prepare(
            'SELECT access_requests.*, users.email AS user_email, api_services.name AS api_name, api_services.slug
             FROM access_requests
             INNER JOIN users ON users.id = access_requests.user_id
             INNER JOIN api_services ON api_services.id = access_requests.api_service_id
             WHERE access_requests.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $result = $statement->fetch();

        return is_array($result) ? $result : null;
    }
}