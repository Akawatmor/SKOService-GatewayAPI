<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

final class FileSnapshot extends BaseModel
{
    protected static string $table = 'file_snapshots';

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function forService(int $serviceId): array
    {
        return static::where(['api_service_id' => $serviceId], 'ORDER BY generated_at DESC');
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findForServiceAndFile(int $serviceId, string $filename): ?array
    {
        return static::firstWhere(['api_service_id' => $serviceId, 'filename' => $filename]);
    }
}