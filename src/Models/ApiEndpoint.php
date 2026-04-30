<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

final class ApiEndpoint extends BaseModel
{
    protected static string $table = 'api_endpoints';

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function forService(int $serviceId): array
    {
        return static::where(['api_service_id' => $serviceId], 'ORDER BY path ASC, method ASC');
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findForService(int $endpointId, int $serviceId): ?array
    {
        return static::firstWhere(['id' => $endpointId, 'api_service_id' => $serviceId]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findHealthCandidateForService(int $serviceId): ?array
    {
        $statement = static::pdo()->prepare(
            'SELECT *
             FROM api_endpoints
             WHERE api_service_id = :api_service_id
             AND method IN ("GET", "HEAD")
             AND path IN ("/health", "/healthz", "/status", "/ping")
             ORDER BY CASE path
                WHEN "/health" THEN 1
                WHEN "/healthz" THEN 2
                WHEN "/status" THEN 3
                ELSE 4
             END
             LIMIT 1'
        );
        $statement->execute(['api_service_id' => $serviceId]);
        $result = $statement->fetch();

        return is_array($result) ? $result : null;
    }
}