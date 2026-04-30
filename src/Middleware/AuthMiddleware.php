<?php

declare(strict_types=1);

namespace GatewayAPI\Middleware;

use Closure;
use GatewayAPI\Core\Request;
use GatewayAPI\Core\Response;
use GatewayAPI\Services\AuthService;

final class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AuthService::currentUser() === null) {
            if ($request->expectsJson()) {
                return Response::json(['message' => 'Authentication required.'], 401);
            }

            flash('error', 'Please log in to continue.');

            return Response::redirect('/login');
        }

        $response = $next($request);

        return $response instanceof Response ? $response : Response::html((string) $response);
    }
}