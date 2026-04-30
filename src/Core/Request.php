<?php

declare(strict_types=1);

namespace GatewayAPI\Core;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $request
     * @param array<string, mixed> $server
     * @param array<string, string> $headers
     * @param array<string, string> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed> $session
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $request,
        private readonly array $server,
        private readonly array $headers,
        private readonly array $cookies,
        private readonly array $files,
        private readonly array $session,
        private readonly string $rawBody,
    ) {
    }

    public static function capture(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path === '' ? '/' : $path,
            $_GET,
            $_POST,
            $_SERVER,
            array_change_key_case($headers, CASE_LOWER),
            $_COOKIE,
            $_FILES,
            $_SESSION,
            file_get_contents('php://input') ?: ''
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<string, mixed>
     */
    public function query(): array
    {
        return $this->query;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $json = $this->json();

        if (array_key_exists($key, $this->request)) {
            return $this->request[$key];
        }

        if (is_array($json) && array_key_exists($key, $json)) {
            return $json[$key];
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [...$this->query, ...$this->request, ...($this->json() ?? [])];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function json(): ?array
    {
        if ($this->rawBody === '') {
            return null;
        }

        $decoded = json_decode($this->rawBody, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function header(string $key, ?string $default = null): ?string
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function cookie(string $key, ?string $default = null): ?string
    {
        $value = $this->cookies[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function file(string $key): ?array
    {
        $value = $this->files[$key] ?? null;

        return is_array($value) ? $value : null;
    }

    public function expectsJson(): bool
    {
        $accept = strtolower($this->header('accept', ''));
        $contentType = strtolower($this->header('content-type', ''));

        return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json') || str_starts_with($this->path, '/api/');
    }

    public function queryParam(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('authorization');

        if ($authorization === null || !str_starts_with($authorization, 'Bearer ')) {
            return null;
        }

        return substr($authorization, 7);
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    /**
     * @return array<string, mixed>
     */
    public function session(): array
    {
        return $this->session;
    }
}