<?php

declare(strict_types=1);

namespace GatewayAPI\Helpers;

final class Sanitizer
{
    public static function string(mixed $value): string
    {
        return trim((string) $value);
    }

    public static function multiline(mixed $value): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
    }

    public static function slug(mixed $value): string
    {
        $slug = strtolower(trim((string) $value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: '';

        return trim($slug, '-');
    }

    public static function filename(mixed $value): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]+/', '-', (string) $value) ?: 'file';
        $filename = trim($filename, '.-');

        return $filename === '' ? 'file' : $filename;
    }
}