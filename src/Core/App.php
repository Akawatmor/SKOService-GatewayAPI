<?php

declare(strict_types=1);

namespace GatewayAPI\Core;

final class App
{
    private static string $rootPath;

    /**
     * @var array<string, mixed>
     */
    private static array $config = [];

    /**
     * @param array<string, mixed> $config
     */
    public static function boot(string $rootPath, array $config): void
    {
        self::$rootPath = $rootPath;
        self::$config = $config;
    }

    public static function rootPath(string $path = ''): string
    {
        return self::join(self::$rootPath, $path);
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public static function storagePath(string $path = ''): string
    {
        return self::join(self::$rootPath . '/storage', $path);
    }

    public static function databasePath(string $path = ''): string
    {
        return self::join(self::$rootPath . '/database', $path);
    }

    private static function join(string $base, string $path): string
    {
        if ($path === '') {
            return $base;
        }

        return rtrim($base, '/\\') . '/' . ltrim($path, '/\\');
    }
}