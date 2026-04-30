<?php

declare(strict_types=1);

namespace GatewayAPI\Middleware;

use Closure;
use GatewayAPI\Core\Request;
use GatewayAPI\Core\Response;
use GatewayAPI\Services\AuthService;

final class RbacMiddleware
{
    public function handle(Request $request, Closure $next, string $requiredRole = 'developer'): Response
    {
        $user = AuthService::currentUser();

        if ($user === null) {
            return $request->expectsJson()
                ? Response::json(['message' => 'Authentication required.'], 401)
                : Response::redirect('/login');
        }

        $roles = ['guest' => 1, 'developer' => 2, 'admin' => 3];
        $currentRole = $roles[$user['role_name'] ?? 'guest'] ?? 1;
        $targetRole = $roles[$requiredRole] ?? 2;

        if ($currentRole < $targetRole) {
            return $request->expectsJson()
                ? Response::json(['message' => 'Forbidden.'], 403)
                : Response::html('<h1>403 Forbidden</h1><p>Access denied.</p>', 403);
        }

        $response = $next($request);

        return $response instanceof Response ? $response : Response::html((string) $response);
    }
}