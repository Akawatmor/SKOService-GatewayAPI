<?php

declare(strict_types=1);

namespace GatewayAPI\Core;

use RuntimeException;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = [], ?string $layout = 'base'): string
    {
        $pagePath = App::rootPath('templates/pages/' . $template . '.php');

        if (!is_file($pagePath)) {
            throw new RuntimeException(sprintf('Template %s not found.', $template));
        }

        $content = self::capture($pagePath, $data);

        if ($layout === null) {
            return $content;
        }

        $layoutPath = App::rootPath('templates/layouts/' . $layout . '.php');

        if (!is_file($layoutPath)) {
            throw new RuntimeException(sprintf('Layout %s not found.', $layout));
        }

        return self::capture($layoutPath, [...$data, 'content' => $content]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function renderComponent(string $component, array $data = []): string
    {
        $componentPath = App::rootPath('templates/components/' . $component . '.php');

        if (!is_file($componentPath)) {
            throw new RuntimeException(sprintf('Component %s not found.', $component));
        }

        return self::capture($componentPath, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function capture(string $filePath, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $filePath;

        return (string) ob_get_clean();
    }
}