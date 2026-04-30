<?php

declare(strict_types=1);

namespace GatewayAPI\Middleware;

use Closure;
use GatewayAPI\Core\Request;
use GatewayAPI\Core\Response;
use GatewayAPI\Models\ApiService;
use GatewayAPI\Models\RateLimit;
use GatewayAPI\Models\RequestLog;
use GatewayAPI\Services\AuthService;

final class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next, string ...$params): Response
    {
        $user = AuthService::currentUser();
        $service = $this->resolveService($request, $params);
        $limits = RateLimit::resolve($service['id'] ?? null, $user['role_id'] ?? null);

        $minuteUsage = RequestLog::countWindow($service['id'] ?? null, $user['id'] ?? null, $request->ip(), 'minute');
        $dayUsage = RequestLog::countWindow($service['id'] ?? null, $user['id'] ?? null, $request->ip(), 'day');

        if ($minuteUsage >= (int) $limits['requests_per_minute'] || $dayUsage >= (int) $limits['requests_per_day']) {
            $response = Response::json([
                'message' => 'Rate limit exceeded.',
                'retry_after' => 60,
            ], 429)->withHeader('Retry-After', '60');

            RequestLog::logRequest([
                'api_service_id' => $service['id'] ?? null,
                'user_id' => $user['id'] ?? null,
                'api_key_prefix' => $user['api_key_prefix'] ?? null,
                'method' => $request->method(),
                'path' => $request->path(),
                'ip_address' => $request->ip(),
                'status_code' => 429,
                'response_time_ms' => 0,
            ]);

            return $response;
        }

        $startedAt = microtime(true);
        $response = $next($request);
        $duration = (int) round((microtime(true) - $startedAt) * 1000);

        $response = $response instanceof Response ? $response : Response::html((string) $response);

        RequestLog::logRequest([
            'api_service_id' => $service['id'] ?? null,
            'user_id' => $user['id'] ?? null,
            'api_key_prefix' => $user['api_key_prefix'] ?? null,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'status_code' => $response->status(),
            'response_time_ms' => $duration,
        ]);

        return $response;
    }

    /**
     * @param array<int, string> $params
     * @return array<string, mixed>|null
     */
    private function resolveService(Request $request, array $params): ?array
    {
        if (str_starts_with($request->path(), '/api/') || str_starts_with($request->path(), '/proxy/')) {
            $slug = $params[0] ?? null;

            if (is_string($slug) && $slug !== '') {
                return ApiService::findBySlug($slug);
            }
        }

        return null;
    }
}