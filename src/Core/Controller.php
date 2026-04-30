<?php

declare(strict_types=1);

namespace GatewayAPI\Core;

abstract class Controller
{
    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $template, array $data = [], ?string $layout = 'base'): Response
    {
        return Response::html(View::render($template, $data, $layout));
    }

    protected function redirect(string $path): Response
    {
        return Response::redirect($path);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function abort(int $status, string $message): Response
    {
        return Response::html('<h1>' . $status . '</h1><p>' . e($message) . '</p>', $status);
    }
}