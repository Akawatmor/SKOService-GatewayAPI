<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Core\Response;
use GatewayAPI\Helpers\Sanitizer;
use GatewayAPI\Models\AccessGrant;
use GatewayAPI\Models\ApiEndpoint;
use GatewayAPI\Models\ApiService;
use GatewayAPI\Models\FileSnapshot;
use GatewayAPI\Services\ProxyService;
use GatewayAPI\Services\SchemaParserService;
use RuntimeException;

final class ApiDetailController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        $service = ApiService::findBySlug($slug);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        $hasAccess = $this->hasAccess($service);

        return $this->render('api-detail', [
            'service' => $service,
            'hasAccess' => $hasAccess,
            'endpoints' => $hasAccess ? ApiEndpoint::forService((int) $service['id']) : [],
            'schema' => $hasAccess ? SchemaParserService::load($service['schema_path']) : ['exists' => false, 'content' => '', 'language' => 'text', 'filename' => null],
            'snapshots' => $hasAccess && $service['api_type'] === 'File' ? FileSnapshot::forService((int) $service['id']) : [],
        ]);
    }

    public function downloadSnapshot(Request $request, string $slug, string $file): Response
    {
        $service = ApiService::findBySlug($slug);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        if (!$this->hasAccess($service)) {
            return $this->abort(403, 'Snapshot access denied.');
        }

        $file = Sanitizer::filename($file);
        $snapshot = FileSnapshot::findForServiceAndFile((int) $service['id'], $file);

        if ($snapshot === null) {
            return $this->abort(404, 'Snapshot file not found.');
        }

        $path = \GatewayAPI\Core\App::storagePath('snapshots/' . $snapshot['storage_path']);

        if (!is_file($path)) {
            return $this->abort(404, 'Stored snapshot is missing.');
        }

        $body = file_get_contents($path);

        if ($body === false) {
            return $this->abort(500, 'Unable to read snapshot file.');
        }

        return Response::binary($body, (string) $snapshot['mime_type'], (string) $snapshot['filename']);
    }

    public function tryItOut(Request $request, string $slug): Response
    {
        $service = ApiService::findBySlug($slug);

        if ($service === null) {
            return $this->json(['message' => 'API service not found.'], 404);
        }

        if (!$this->hasAccess($service)) {
            return $this->json(['message' => 'Access grant required.'], 403);
        }

        $endpoint = ApiEndpoint::findForService((int) $request->input('endpoint_id', 0), (int) $service['id']);

        if ($endpoint === null) {
            return $this->json(['message' => 'Endpoint not found.'], 404);
        }

        if ((int) $endpoint['auth_required'] === 1 && current_user() === null) {
            return $this->json(['message' => 'Authentication required for this endpoint.'], 401);
        }

        $headers = $this->parseJsonObject((string) $request->input('headers', '{}'));
        $query = $this->parseJsonObject((string) $request->input('query_params', '{}'));
        $body = (string) $request->input('request_body', '');

        try {
            $result = ProxyService::forward(
                $service,
                (string) $endpoint['path'],
                (string) $request->input('method', $endpoint['method']),
                $headers,
                $query,
                $body !== '' ? $body : null
            );
        } catch (RuntimeException $exception) {
            return $this->json(['message' => $exception->getMessage()], 502);
        }

        return $this->json([
            'status_code' => $result['status'],
            'response_time_ms' => $result['response_time_ms'],
            'response_headers' => $result['headers'],
            'response_body' => $this->formatBody($result['body']),
        ]);
    }

    public function healthCheck(Request $request, string $slug): Response
    {
        $service = ApiService::findBySlug($slug);

        if ($service === null) {
            return $this->json(['message' => 'API service not found.'], 404);
        }

        if (!$this->hasAccess($service)) {
            return $this->json(['message' => 'Access grant required.'], 403);
        }

        $health = $this->resolveHealthTarget($service);

        if ($health === null) {
            return $this->json(['message' => 'Health check is not configured for this API.'], 404);
        }

        try {
            $result = ProxyService::forward($service, $health['path'], $health['method']);
        } catch (RuntimeException $exception) {
            ApiService::updateById((int) $service['id'], [
                'last_health_status_code' => 0,
                'last_health_response_time_ms' => 0,
                'last_health_checked_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->json([
                'message' => $exception->getMessage(),
                'healthy' => false,
            ], 502);
        }

        ApiService::updateById((int) $service['id'], [
            'last_health_status_code' => $result['status'],
            'last_health_response_time_ms' => $result['response_time_ms'],
            'last_health_checked_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->json([
            'healthy' => $result['status'] >= 200 && $result['status'] < 400,
            'status_code' => $result['status'],
            'response_time_ms' => $result['response_time_ms'],
            'checked_at' => date('c'),
            'target' => $health,
        ]);
    }

    /**
     * @param array<string, mixed> $service
     */
    private function hasAccess(array $service): bool
    {
        if ((int) $service['is_public'] === 1) {
            return true;
        }

        if (is_admin()) {
            return true;
        }

        $user = current_user();

        return $user !== null && AccessGrant::hasGrant((int) $user['id'], (int) $service['id']);
    }

    /**
     * @return array<string, string>
     */
    private function parseJsonObject(string $value): array
    {
        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_map(static fn ($item): string => (string) $item, $decoded) : [];
    }

    private function formatBody(string $body): string
    {
        $decoded = json_decode($body, true);

        return is_array($decoded)
            ? (json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $body)
            : $body;
    }

    /**
     * @param array<string, mixed> $service
     * @return array{method:string, path:string}|null
     */
    private function resolveHealthTarget(array $service): ?array
    {
        $path = trim((string) ($service['health_check_path'] ?? ''));

        if ($path !== '') {
            return [
                'method' => strtoupper((string) ($service['health_check_method'] ?? 'GET')),
                'path' => $path,
            ];
        }

        $candidate = ApiEndpoint::findHealthCandidateForService((int) $service['id']);

        if ($candidate === null) {
            return null;
        }

        return [
            'method' => strtoupper((string) ($candidate['method'] ?? 'GET')),
            'path' => (string) ($candidate['path'] ?? '/health'),
        ];
    }
}