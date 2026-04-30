<?php

declare(strict_types=1);

namespace GatewayAPI\Services;

use GatewayAPI\Core\App;

final class I18nService
{
    /**
     * @var array<string, array<string, string>>
     */
    private static array $catalog = [];

    private static string $currentLanguage = 'th';

    public static function bootstrap(): void
    {
        $available = ['th', 'en'];
        $candidate = $_GET['lang'] ?? $_COOKIE['lang'] ?? App::config('app.lang.default', 'th');
        $candidate = is_string($candidate) ? strtolower($candidate) : 'th';

        if (!in_array($candidate, $available, true)) {
            $candidate = App::config('app.lang.default', 'th');
        }

        self::$currentLanguage = (string) $candidate;

        setcookie('lang', self::$currentLanguage, [
            'expires' => time() + 31536000,
            'path' => '/',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }

    public static function current(): string
    {
        return self::$currentLanguage;
    }

    public static function trans(string $key, array $replace = []): string
    {
        $catalog = self::catalog(self::$currentLanguage);
        $fallback = self::catalog((string) App::config('app.lang.fallback', 'en'));
        $value = $catalog[$key] ?? $fallback[$key] ?? $key;

        foreach ($replace as $search => $replacement) {
            $value = str_replace(':' . $search, (string) $replacement, $value);
        }

        return $value;
    }

    /**
     * @return array<string, string>
     */
    private static function catalog(string $lang): array
    {
        if (isset(self::$catalog[$lang])) {
            return self::$catalog[$lang];
        }

        $path = App::rootPath('i18n/' . $lang . '.php');
        self::$catalog[$lang] = is_file($path) ? (require $path) : [];

        return self::$catalog[$lang];
    }
}