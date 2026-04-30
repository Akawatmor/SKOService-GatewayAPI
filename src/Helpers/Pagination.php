<?php

declare(strict_types=1);

namespace GatewayAPI\Helpers;

final class Pagination
{
    /**
     * @return array<string, int>
     */
    public static function resolve(int $page, int $perPage, int $total): array
    {
        $page = max($page, 1);
        $perPage = max($perPage, 1);
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min($page, $lastPage);

        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'offset' => ($page - 1) * $perPage,
        ];
    }
}