<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Core\Response;
use GatewayAPI\Models\AccessGrant;
use GatewayAPI\Models\ApiService;
use GatewayAPI\Services\ProxyService;
use RuntimeException;

final class ProxyController extends Controller
{
    public function forward(Request $request, string $slug, string $path = ''): Response
    {
        $service = ApiService::findBySlug($slug);

        if ($service === null) {
            return $this->json(['message' => 'API service not found.'], 404);
        }

        $user = current_user();

        if ($user === null) {
            return $this->json(['message' => 'Authentication required.'], 401);
        }

        if (!is_admin() && (int) $service['is_public'] !== 1 && !AccessGrant::hasGrant((int) $user['id'], (int) $service['id'])) {
            return $this->json(['message' => 'Access grant required.'], 403);
        }

        try {
            $result = ProxyService::forward(
                $service,
                $path,
                $request->method(),
                $this->forwardHeaders($request),
                $request->query(),
                $request->rawBody() !== '' ? $request->rawBody() : null
            );
        } catch (RuntimeException $exception) {
            return $this->json(['message' => $exception->getMessage()], 502);
        }

        return new Response($result['body'], $result['status'], [
            'Content-Type' => $result['headers']['Content-Type'] ?? 'application/json; charset=UTF-8',
            'X-Gateway-Response-Time' => (string) $result['response_time_ms'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function forwardHeaders(Request $request): array
    {
        $headers = [];

        foreach (['content-type', 'accept', 'authorization', 'x-api-key'] as $headerName) {
            $value = $request->header($headerName);

            if ($value !== null) {
                $headers[ucwords($headerName, '-')] = $value;
            }
        }

        return $headers;
    }
}