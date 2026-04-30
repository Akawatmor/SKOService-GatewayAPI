<?php

declare(strict_types=1);

use GatewayAPI\Core\App;
use GatewayAPI\Core\View;
use GatewayAPI\Helpers\Csrf;
use GatewayAPI\Services\AuthService;
use GatewayAPI\Services\I18nService;

if (!function_exists('app_config')) {
    function app_config(string $key, mixed $default = null): mixed
    {
        return App::config($key, $default);
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = [], ?string $layout = 'base'): string
    {
        return View::render($template, $data, $layout);
    }
}

if (!function_exists('component')) {
    function component(string $component, array $data = []): string
    {
        return View::renderComponent($component, $data);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return I18nService::trans($key, $replace);
    }
}

if (!function_exists('lang')) {
    function lang(): string
    {
        return I18nService::current();
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('current_user')) {
    /**
     * @return array<string, mixed>|null
     */
    function current_user(): ?array
    {
        return AuthService::currentUser();
    }
}

if (!function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return current_user() !== null;
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return (current_user()['role_name'] ?? null) === 'admin';
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): string|null
    {
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;

            return null;
        }

        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $value = (string) $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);

        return $value;
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('remember_old')) {
    function remember_old(array $data): void
    {
        $_SESSION['old'] = $data;
    }
}

if (!function_exists('forget_old')) {
    function forget_old(): void
    {
        unset($_SESSION['old']);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(Csrf::token()) . '">';
    }
}

if (!function_exists('selected')) {
    function selected(mixed $left, mixed $right): string
    {
        return $left === $right ? 'selected' : '';
    }
}

if (!function_exists('checked')) {
    function checked(bool $state): string
    {
        return $state ? 'checked' : '';
    }
}

if (!function_exists('localized_text')) {
    /**
     * @param array<string, mixed> $row
     */
    function localized_text(array $row, string $field): string
    {
        $primary = $field . '_' . lang();
        $fallback = $field . '_' . app_config('app.lang.fallback', 'en');

        return (string) ($row[$primary] ?? $row[$fallback] ?? $row[$field] ?? '');
    }
}