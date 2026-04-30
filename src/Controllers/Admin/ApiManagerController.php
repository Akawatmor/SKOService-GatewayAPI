<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers\Admin;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Helpers\Csrf;
use GatewayAPI\Helpers\Sanitizer;
use GatewayAPI\Helpers\Validator;
use GatewayAPI\Models\ApiEndpoint;
use GatewayAPI\Models\ApiService;
use GatewayAPI\Models\FileSnapshot;
use GatewayAPI\Services\SnapshotService;

final class ApiManagerController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        $context = $this->managementContext($request);
        $user = $this->currentManager();

        return $this->render('admin/api-list', [
            ...$context,
            'services' => ApiService::manageableListForUser($user),
        ], $context['layout']);
    }

    public function create(Request $request): \GatewayAPI\Core\Response
    {
        $context = $this->managementContext($request);

        return $this->render('admin/api-edit', [
            ...$context,
            'service' => null,
            'endpoints' => [],
            'snapshots' => [],
        ], $context['layout']);
    }

    public function edit(Request $request, string $id): \GatewayAPI\Core\Response
    {
        $service = ApiService::find((int) $id);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        $authorization = $this->authorizeService($service);
        if ($authorization !== null) {
            return $authorization;
        }

        $context = $this->managementContext($request);

        return $this->render('admin/api-edit', [
            ...$context,
            'service' => $service,
            'endpoints' => ApiEndpoint::forService((int) $service['id']),
            'snapshots' => FileSnapshot::forService((int) $service['id']),
        ], $context['layout']);
    }

    public function store(Request $request): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect('/admin/apis');
        }

        $payload = $this->servicePayload($request);
        $errors = Validator::validateApiService($payload);

        if ($errors !== []) {
            flash('error', implode(' ', $errors));
            return $this->redirect('/admin/apis/create');
        }

        $payload['created_by'] = current_user()['id'] ?? null;
        $payload['created_at'] = date('Y-m-d H:i:s');
        $serviceId = ApiService::insert($payload);

        flash('success', 'API service created.');
        return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $serviceId . '/edit');
    }

    public function update(Request $request, string $id): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect('/admin/apis');
        }

        $service = ApiService::find((int) $id);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        $authorization = $this->authorizeService($service);
        if ($authorization !== null) {
            return $authorization;
        }

        $payload = $this->servicePayload($request);
        $errors = Validator::validateApiService($payload);

        if ($errors !== []) {
            flash('error', implode(' ', $errors));
            return $this->redirect('/admin/apis/' . $id . '/edit');
        }

        $payload['updated_at'] = date('Y-m-d H:i:s');
        ApiService::updateById((int) $id, $payload);

        flash('success', 'API service updated.');
        return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
    }

    public function delete(Request $request, string $id): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect($this->managementContext($request)['route_prefix']);
        }

        $service = ApiService::find((int) $id);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        $authorization = $this->authorizeService($service);
        if ($authorization !== null) {
            return $authorization;
        }

        ApiService::deleteById((int) $id);
        flash('success', 'API service deleted.');
        return $this->redirect($this->managementContext($request)['route_prefix']);
    }

    public function storeEndpoint(Request $request, string $id): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
        }

        $service = ApiService::find((int) $id);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        $authorization = $this->authorizeService($service);
        if ($authorization !== null) {
            return $authorization;
        }

        ApiEndpoint::insert([
            'api_service_id' => (int) $id,
            'method' => strtoupper(Sanitizer::string($request->input('method', 'GET'))),
            'path' => Sanitizer::string($request->input('path', '/')),
            'description_th' => Sanitizer::multiline($request->input('description_th', '')),
            'description_en' => Sanitizer::multiline($request->input('description_en', '')),
            'auth_required' => $request->input('auth_required') ? 1 : 0,
            'request_schema' => Sanitizer::multiline($request->input('request_schema', '{}')),
            'response_schema' => Sanitizer::multiline($request->input('response_schema', '{}')),
            'example_request' => Sanitizer::multiline($request->input('example_request', '')),
            'example_response' => Sanitizer::multiline($request->input('example_response', '')),
            'is_active' => 1,
        ]);

        flash('success', 'Endpoint added.');
        return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
    }

    public function deleteEndpoint(Request $request, string $id, string $endpointId): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
        }

        $service = ApiService::find((int) $id);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        $authorization = $this->authorizeService($service);
        if ($authorization !== null) {
            return $authorization;
        }

        ApiEndpoint::deleteById((int) $endpointId);
        flash('success', 'Endpoint deleted.');
        return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
    }

    public function uploadSchema(Request $request, string $id): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
        }

        $service = ApiService::find((int) $id);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        $authorization = $this->authorizeService($service);
        if ($authorization !== null) {
            return $authorization;
        }

        $uploaded = $request->file('schema_file');
        $filename = Sanitizer::filename($request->input('schema_filename', $uploaded['name'] ?? $service['slug'] . '.txt'));
        $target = \GatewayAPI\Core\App::storagePath('schemas/' . $filename);
        $saved = false;

        if (is_array($uploaded) && ($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && isset($uploaded['tmp_name'])) {
            $saved = move_uploaded_file((string) $uploaded['tmp_name'], $target);
        } else {
            $content = (string) $request->input('schema_content', '');
            if ($content !== '') {
                $saved = file_put_contents($target, $content) !== false;
            }
        }

        if (!$saved) {
            flash('error', 'Unable to save schema file.');
            return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
        }

        ApiService::updateById((int) $id, ['schema_path' => $filename, 'updated_at' => date('Y-m-d H:i:s')]);
        flash('success', 'Schema saved.');
        return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
    }

    public function generateSnapshot(Request $request, string $id): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
        }

        $service = ApiService::find((int) $id);

        if ($service === null) {
            return $this->abort(404, 'API service not found.');
        }

        $authorization = $this->authorizeService($service);
        if ($authorization !== null) {
            return $authorization;
        }

        SnapshotService::generate($service, (int) (current_user()['id'] ?? 0));
        flash('success', 'Snapshot generated.');
        return $this->redirect($this->managementContext($request)['route_prefix'] . '/' . $id . '/edit');
    }

    /**
     * @return array<string, mixed>
     */
    private function servicePayload(Request $request): array
    {
        $tags = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('tags', '')))));

        return [
            'name' => Sanitizer::string($request->input('name', '')),
            'slug' => Sanitizer::slug($request->input('slug', '')),
            'description_th' => Sanitizer::multiline($request->input('description_th', '')),
            'description_en' => Sanitizer::multiline($request->input('description_en', '')),
            'mode' => Sanitizer::string($request->input('mode', 'catalog')),
            'api_type' => Sanitizer::string($request->input('api_type', 'REST')),
            'standard' => Sanitizer::string($request->input('standard', 'none')),
            'status' => Sanitizer::string($request->input('status', 'active')),
            'base_url' => Sanitizer::string($request->input('base_url', '')) ?: null,
            'version' => Sanitizer::string($request->input('version', '1.0')),
            'health_check_method' => Sanitizer::string($request->input('health_check_method', 'GET')) ?: null,
            'health_check_path' => Sanitizer::string($request->input('health_check_path', '')) ?: null,
            'is_public' => $request->input('is_public') ? 1 : 0,
            'tags' => json_encode($tags, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function managementContext(Request $request): array
    {
        $admin = str_starts_with($request->path(), '/admin/');

        return [
            'layout' => $admin ? 'admin' : 'base',
            'route_prefix' => $admin ? '/admin/apis' : '/workspace/apis',
            'section_label' => $admin ? 'Admin API Services' : 'Developer API Workspace',
            'management_scope' => $admin ? 'all' : 'owned',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function currentManager(): array
    {
        $user = current_user();

        return is_array($user) ? $user : [];
    }

    private function authorizeService(array $service): ?\GatewayAPI\Core\Response
    {
        $user = $this->currentManager();

        if ($user === [] || !ApiService::isManageableByUser($service, $user)) {
            flash('error', 'You do not have permission to manage this API service.');

            return $this->abort(403, 'Access denied for this API service.');
        }

        return null;
    }
}