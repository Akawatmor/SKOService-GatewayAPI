<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

use GatewayAPI\Core\Database;
use PDO;

abstract class BaseModel
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    protected static function pdo(): PDO
    {
        return Database::connection();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(string $orderBy = 'id DESC'): array
    {
        $sql = sprintf('SELECT * FROM %s ORDER BY %s', static::$table, $orderBy);
        $statement = static::pdo()->query($sql);

        return $statement !== false ? $statement->fetchAll() : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int|string $id): ?array
    {
        $statement = static::pdo()->prepare(sprintf('SELECT * FROM %s WHERE %s = :id LIMIT 1', static::$table, static::$primaryKey));
        $statement->execute(['id' => $id]);
        $result = $statement->fetch();

        return is_array($result) ? $result : null;
    }

    /**
     * @param array<string, mixed> $conditions
     * @return array<int, array<string, mixed>>
     */
    public static function where(array $conditions, string $extra = '', array $bindings = []): array
    {
        [$whereSql, $whereBindings] = static::buildWhere($conditions);
        $sql = sprintf('SELECT * FROM %s %s %s', static::$table, $whereSql, $extra);
        $statement = static::pdo()->prepare(trim($sql));
        $statement->execute([...$whereBindings, ...$bindings]);

        return $statement->fetchAll();
    }

    /**
     * @param array<string, mixed> $conditions
     * @return array<string, mixed>|null
     */
    public static function firstWhere(array $conditions, string $extra = '', array $bindings = []): ?array
    {
        $results = static::where($conditions, trim($extra . ' LIMIT 1'), $bindings);

        return $results[0] ?? null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $statement = static::pdo()->prepare($sql);
        $statement->execute($data);

        return (int) static::pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function updateById(int|string $id, array $data): void
    {
        if ($data === []) {
            return;
        }

        $assignments = array_map(static fn (string $column): string => $column . ' = :' . $column, array_keys($data));
        $data['__id'] = $id;
        $sql = sprintf('UPDATE %s SET %s WHERE %s = :__id', static::$table, implode(', ', $assignments), static::$primaryKey);
        $statement = static::pdo()->prepare($sql);
        $statement->execute($data);
    }

    public static function deleteById(int|string $id): void
    {
        $statement = static::pdo()->prepare(sprintf('DELETE FROM %s WHERE %s = :id', static::$table, static::$primaryKey));
        $statement->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $conditions
     */
    public static function count(array $conditions = []): int
    {
        [$whereSql, $bindings] = static::buildWhere($conditions);
        $statement = static::pdo()->prepare(sprintf('SELECT COUNT(*) FROM %s %s', static::$table, $whereSql));
        $statement->execute($bindings);

        return (int) $statement->fetchColumn();
    }

    /**
     * @param array<string, mixed> $conditions
     * @return array{0:string, 1:array<string, mixed>}
     */
    protected static function buildWhere(array $conditions): array
    {
        if ($conditions === []) {
            return ['', []];
        }

        $clauses = [];
        $bindings = [];

        foreach ($conditions as $column => $value) {
            $placeholder = str_replace('.', '_', $column);
            $clauses[] = $column . ' = :' . $placeholder;
            $bindings[$placeholder] = $value;
        }

        return ['WHERE ' . implode(' AND ', $clauses), $bindings];
    }
}