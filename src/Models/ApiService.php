<?php

declare(strict_types=1);

namespace GatewayAPI\Models;

use GatewayAPI\Helpers\Pagination;
use PDO;

final class ApiService extends BaseModel
{
    protected static string $table = 'api_services';

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function manageableListForUser(array $user): array
    {
        if (($user['role_name'] ?? null) === 'admin') {
            return static::adminList();
        }

        $statement = static::pdo()->prepare(
            'SELECT api_services.*, COUNT(api_endpoints.id) AS endpoint_count
             FROM api_services
             LEFT JOIN api_endpoints ON api_endpoints.api_service_id = api_services.id
             WHERE api_services.created_by = :user_id
             GROUP BY api_services.id
             ORDER BY api_services.created_at DESC'
        );
        $statement->execute(['user_id' => $user['id']]);

        return $statement->fetchAll();
    }

    public static function isManageableByUser(array $service, array $user): bool
    {
        return ($user['role_name'] ?? null) === 'admin'
            || (int) ($service['created_by'] ?? 0) === (int) ($user['id'] ?? 0);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $statement = static::pdo()->prepare(
            'SELECT api_services.*, COUNT(api_endpoints.id) AS endpoint_count
             FROM api_services
             LEFT JOIN api_endpoints ON api_endpoints.api_service_id = api_services.id
             WHERE api_services.slug = :slug
             GROUP BY api_services.id
             LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $result = $statement->fetch();

        return is_array($result) ? $result : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function featured(int $limit = 6): array
    {
        $statement = static::pdo()->prepare(
            'SELECT api_services.*, COUNT(api_endpoints.id) AS endpoint_count
             FROM api_services
             LEFT JOIN api_endpoints ON api_endpoints.api_service_id = api_services.id
             WHERE api_services.is_public = 1 AND api_services.status = :status
             GROUP BY api_services.id
             ORDER BY api_services.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue(':status', 'active');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{items:array<int, array<string, mixed>>, pagination:array<string, int>}
     */
    public static function searchCatalog(array $filters, ?array $user = null, int $page = 1, int $perPage = 10): array
    {
        $conditions = ['1 = 1'];
        $bindings = [];

        if ($user === null) {
            $conditions[] = 'api_services.is_public = 1';
        } elseif (($filters['visibility'] ?? '') === 'public') {
            $conditions[] = 'api_services.is_public = 1';
        } elseif (($filters['visibility'] ?? '') === 'private') {
            $conditions[] = 'api_services.is_public = 0';
        }

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $conditions[] = '(api_services.name LIKE :search OR api_services.description_th LIKE :search OR api_services.description_en LIKE :search OR api_services.tags LIKE :search)';
            $bindings['search'] = '%' . $search . '%';
        }

        foreach (['api_type', 'standard', 'status'] as $column) {
            $value = trim((string) ($filters[$column] ?? ''));

            if ($value !== '') {
                $conditions[] = 'api_services.' . $column . ' = :' . $column;
                $bindings[$column] = $value;
            }
        }

        $orderBy = match ($filters['sort'] ?? 'newest') {
            'name' => 'api_services.name ASC',
            'most_used' => 'usage_count DESC, api_services.name ASC',
            default => 'api_services.created_at DESC',
        };

        $whereSql = 'WHERE ' . implode(' AND ', $conditions);

        $countStatement = static::pdo()->prepare(
            'SELECT COUNT(*)
             FROM api_services
             ' . $whereSql
        );
        $countStatement->execute($bindings);
        $total = (int) $countStatement->fetchColumn();
        $pagination = Pagination::resolve($page, $perPage, $total);

        $sql = 'SELECT api_services.*, COUNT(DISTINCT api_endpoints.id) AS endpoint_count, COUNT(DISTINCT request_logs.id) AS usage_count
                FROM api_services
                LEFT JOIN api_endpoints ON api_endpoints.api_service_id = api_services.id
                LEFT JOIN request_logs ON request_logs.api_service_id = api_services.id
                ' . $whereSql . '
                GROUP BY api_services.id
                ORDER BY ' . $orderBy . '
                LIMIT :limit OFFSET :offset';

        $statement = static::pdo()->prepare($sql);

        foreach ($bindings as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }

        $statement->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
        $statement->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'pagination' => $pagination,
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function stats(): array
    {
        $pdo = static::pdo();

        return [
            'total_apis' => (int) $pdo->query('SELECT COUNT(*) FROM api_services')->fetchColumn(),
            'total_endpoints' => (int) $pdo->query('SELECT COUNT(*) FROM api_endpoints')->fetchColumn(),
            'total_public' => (int) $pdo->query('SELECT COUNT(*) FROM api_services WHERE is_public = 1')->fetchColumn(),
            'total_private' => (int) $pdo->query('SELECT COUNT(*) FROM api_services WHERE is_public = 0')->fetchColumn(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminList(): array
    {
        $statement = static::pdo()->query(
            'SELECT api_services.*, COUNT(api_endpoints.id) AS endpoint_count
             FROM api_services
             LEFT JOIN api_endpoints ON api_endpoints.api_service_id = api_services.id
             GROUP BY api_services.id
             ORDER BY api_services.created_at DESC'
        );

        return $statement !== false ? $statement->fetchAll() : [];
    }
}