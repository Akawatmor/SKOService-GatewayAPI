<?php

declare(strict_types=1);

namespace GatewayAPI\Middleware;

use Closure;
use GatewayAPI\Core\Request;
use GatewayAPI\Core\Response;

final class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->method() === 'OPTIONS') {
            return Response::html('', 204)->withHeaders($this->headers());
        }

        $response = $next($request);
        $response = $response instanceof Response ? $response : Response::html((string) $response);

        return $response->withHeaders($this->headers());
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-API-Key',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
        ];
    }
}