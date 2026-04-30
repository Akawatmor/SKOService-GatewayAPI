<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

use PDO;

final class User extends BaseModel
{
    protected static string $table = 'users';

    /**
     * @return array<string, mixed>|null
     */
    public static function findByEmail(string $email): ?array
    {
        return static::firstWhere(['email' => $email]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findByApiKeyHash(string $hash): ?array
    {
        return static::firstWhere(['api_key' => $hash]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findWithRole(int $id): ?array
    {
        $statement = static::pdo()->prepare(
            'SELECT users.*, roles.name AS role_name, roles.permissions AS role_permissions
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $result = $statement->fetch();

        return is_array($result) ? $result : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function allWithRoles(): array
    {
        $statement = static::pdo()->query(
            'SELECT users.*, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             ORDER BY users.created_at DESC'
        );

        return $statement !== false ? $statement->fetchAll() : [];
    }

    public static function developerCount(): int
    {
        $statement = static::pdo()->query(
            "SELECT COUNT(*) FROM users INNER JOIN roles ON roles.id = users.role_id WHERE roles.name IN ('developer', 'admin')"
        );

        return $statement !== false ? (int) $statement->fetchColumn() : 0;
    }

    public static function updateRefreshToken(int $id, string $refreshToken): void
    {
        static::updateById($id, ['refresh_token' => $refreshToken]);
    }

    public static function countAll(): int
    {
        return static::count();
    }
}