<?php

declare(strict_types=1);

namespace GatewayAPI\Services;

use GatewayAPI\Core\App;
use GatewayAPI\Models\FileSnapshot;
use RuntimeException;

final class SnapshotService
{
    /**
     * @param array<string, mixed> $service
     * @return array<string, mixed>
     */
    public static function generate(array $service, int $userId): array
    {
        $source = App::databasePath('gatewayapi.sqlite3');

        if (!is_file($source)) {
            throw new RuntimeException('Source SQLite database not found.');
        }

        $filename = sprintf('%s-%s.sqlite3', $service['slug'], date('Ymd-His'));
        $storageName = bin2hex(random_bytes(8)) . '-' . $filename;
        $destination = App::storagePath('snapshots/' . $storageName);

        if (!copy($source, $destination)) {
            throw new RuntimeException('Unable to generate snapshot file.');
        }

        FileSnapshot::insert([
            'api_service_id' => $service['id'],
            'filename' => $filename,
            'mime_type' => 'application/vnd.sqlite3',
            'storage_path' => $storageName,
            'size_bytes' => filesize($destination) ?: 0,
            'generated_by' => $userId,
        ]);

        return FileSnapshot::findForServiceAndFile((int) $service['id'], $filename) ?? [];
    }
}