<?php

declare(strict_types=1);

namespace GatewayAPI\Core;

use Closure;
use RuntimeException;

final class Router
{
    /**
     * @var array<int, array{method:string, pattern:string, handler:callable, middleware:array<int, string>}>
     */
    private array $routes = [];

    /**
     * @param array<int, string> $middleware
     */
    public function get(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    /**
     * @param array<int, string> $middleware
     */
    public function post(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    /**
     * @param array<int, string> $middleware
     */
    public function any(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('*', $pattern, $handler, $middleware);
    }

    /**
     * @param array<int, string> $middleware
     */
    public function add(string $method, string $pattern, callable $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== '*' && $route['method'] !== $request->method()) {
                continue;
            }

            $matches = $this->match($route['pattern'], $request->path());

            if ($matches === null) {
                continue;
            }

            $handler = $this->wrapMiddleware($route['handler'], $route['middleware']);
            $response = $handler($request, ...array_values($matches));

            if ($response instanceof Response) {
                return $response;
            }

            return Response::html((string) $response);
        }

        return Response::html('<h1>404 Not Found</h1>', 404);
    }

    /**
     * @return array<string, string>|null
     */
    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}/', static function (array $parts): string {
            $name = $parts[1];
            $rule = $parts[2] ?? '[^/]+';

            return sprintf('(?P<%s>%s)', $name, $rule);
        }, $pattern);

        if ($regex === null) {
            throw new RuntimeException('Unable to compile route pattern.');
        }

        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        return array_filter($matches, static fn (string|int $key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<int, string> $middlewareClasses
     */
    private function wrapMiddleware(callable $handler, array $middlewareClasses): callable
    {
        return array_reduce(
            array_reverse($middlewareClasses),
            static function (callable $next, string $middlewareDefinition): callable {
                return static function (Request $request, mixed ...$params) use ($middlewareDefinition, $next): mixed {
                    [$middlewareClass, $rawArguments] = array_pad(explode(':', $middlewareDefinition, 2), 2, null);
                    $arguments = $rawArguments === null || $rawArguments === '' ? [] : explode(',', $rawArguments);

                    if (!class_exists($middlewareClass)) {
                        throw new RuntimeException(sprintf('Middleware %s not found.', $middlewareClass));
                    }

                    $middleware = new $middlewareClass();

                    if (!method_exists($middleware, 'handle')) {
                        throw new RuntimeException(sprintf('Middleware %s must define handle().', $middlewareClass));
                    }

                    return $middleware->handle(
                        $request,
                        static fn (Request $request) => $next($request, ...$params),
                        ...$arguments,
                        ...$params
                    );
                };
            },
            $handler
        );
    }
}