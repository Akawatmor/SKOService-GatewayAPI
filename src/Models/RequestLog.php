<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

use DateTimeImmutable;

final class RequestLog extends BaseModel
{
    protected static string $table = 'request_logs';

    /**
     * @param array<string, mixed> $data
     */
    public static function logRequest(array $data): void
    {
        static::insert([
            'api_service_id' => $data['api_service_id'] ?? null,
            'endpoint_id' => $data['endpoint_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'api_key_prefix' => $data['api_key_prefix'] ?? null,
            'method' => $data['method'] ?? null,
            'path' => $data['path'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'status_code' => $data['status_code'] ?? null,
            'response_time_ms' => $data['response_time_ms'] ?? null,
        ]);
    }

    public static function countWindow(?int $serviceId, ?int $userId, string $ipAddress, string $window): int
    {
        $threshold = match ($window) {
            'minute' => (new DateTimeImmutable('-1 minute'))->format('Y-m-d H:i:s'),
            default => (new DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s'),
        };

        $sql = 'SELECT COUNT(*) FROM request_logs WHERE created_at >= :threshold';
        $bindings = ['threshold' => $threshold];

        if ($serviceId !== null) {
            $sql .= ' AND api_service_id = :service_id';
            $bindings['service_id'] = $serviceId;
        }

        if ($userId !== null) {
            $sql .= ' AND user_id = :user_id';
            $bindings['user_id'] = $userId;
        } else {
            $sql .= ' AND ip_address = :ip_address';
            $bindings['ip_address'] = $ipAddress;
        }

        $statement = static::pdo()->prepare($sql);
        $statement->execute($bindings);

        return (int) $statement->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function recent(int $limit = 20): array
    {
        $statement = static::pdo()->prepare(
            'SELECT request_logs.*, api_services.name AS api_name, users.email AS user_email
             FROM request_logs
             LEFT JOIN api_services ON api_services.id = request_logs.api_service_id
             LEFT JOIN users ON users.id = request_logs.user_id
             ORDER BY request_logs.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function forUser(int $userId, int $limit = 20): array
    {
        $statement = static::pdo()->prepare(
            'SELECT request_logs.*, api_services.name AS api_name
             FROM request_logs
             LEFT JOIN api_services ON api_services.id = request_logs.api_service_id
             WHERE request_logs.user_id = :user_id
             ORDER BY request_logs.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}