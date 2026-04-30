<?php

declare(strict_types=1);

namespace GatewayAPI\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;
    private static bool $bootstrapped = false;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $databasePath = (string) App::config('database.path');
        $directory = dirname($databasePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        self::$connection = new PDO('sqlite:' . $databasePath);
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::bootstrapSchema();

        return self::$connection;
    }

    private static function bootstrapSchema(): void
    {
        if (self::$bootstrapped) {
            return;
        }

        $pdo = self::$connection;

        if (!$pdo instanceof PDO) {
            throw new RuntimeException('Database connection not available.');
        }

        $pdo->exec('CREATE TABLE IF NOT EXISTS __migrations (filename TEXT PRIMARY KEY, ran_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');

        $migrationFiles = glob(App::databasePath('migrations/*.sql')) ?: [];
        sort($migrationFiles);

        foreach ($migrationFiles as $migrationFile) {
            $filename = basename($migrationFile);
            $statement = $pdo->prepare('SELECT COUNT(*) FROM __migrations WHERE filename = :filename');
            $statement->execute(['filename' => $filename]);

            if ((int) $statement->fetchColumn() > 0) {
                continue;
            }

            $sql = file_get_contents($migrationFile);

            if ($sql === false) {
                throw new RuntimeException(sprintf('Unable to read migration file %s.', $migrationFile));
            }

            $pdo->exec($sql);
            $insert = $pdo->prepare('INSERT INTO __migrations (filename) VALUES (:filename)');
            $insert->execute(['filename' => $filename]);
        }

        $countQuery = $pdo->query('SELECT COUNT(*) FROM roles');
        $roleCount = $countQuery !== false ? (int) $countQuery->fetchColumn() : 0;

        if ($roleCount === 0) {
            $seedSql = file_get_contents(App::databasePath('seeds/demo_data.sql'));

            if ($seedSql === false) {
                throw new RuntimeException('Unable to read seed file.');
            }

            $pdo->exec($seedSql);
        }

        self::$bootstrapped = true;
    }
}