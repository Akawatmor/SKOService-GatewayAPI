<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Helpers\Csrf;
use GatewayAPI\Helpers\Sanitizer;
use GatewayAPI\Models\AccessGrant;
use GatewayAPI\Models\AccessRequest;
use GatewayAPI\Models\ApiService;

final class AccessRequestController extends Controller
{
    public function store(Request $request): \GatewayAPI\Core\Response
    {
        $user = current_user();

        if ($user === null) {
            return $this->redirect('/login');
        }

        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');

            return $this->redirect('/dashboard');
        }

        $service = null;
        $serviceId = (int) $request->input('api_service_id', 0);

        if ($serviceId > 0) {
            $service = ApiService::find($serviceId);
        }

        if ($service === null && is_string($request->input('slug'))) {
            $service = ApiService::findBySlug((string) $request->input('slug'));
        }

        if ($service === null) {
            flash('error', 'API service not found.');

            return $this->redirect('/search');
        }

        if (AccessGrant::hasGrant((int) $user['id'], (int) $service['id'])) {
            flash('success', 'You already have access to this API.');

            return $this->redirect('/api/' . $service['slug']);
        }

        $existing = AccessRequest::firstWhere([
            'user_id' => $user['id'],
            'api_service_id' => $service['id'],
            'status' => 'pending',
        ]);

        if ($existing !== null) {
            flash('success', 'An access request is already pending.');

            return $this->redirect('/access-request/' . $existing['id']);
        }

        $requestId = AccessRequest::insert([
            'user_id' => $user['id'],
            'api_service_id' => $service['id'],
            'reason' => Sanitizer::multiline($request->input('reason', '')),
            'status' => 'pending',
            'requested_at' => date('Y-m-d H:i:s'),
        ]);

        flash('success', 'Access request submitted.');

        return $this->redirect('/access-request/' . $requestId);
    }

    public function show(Request $request, string $id): \GatewayAPI\Core\Response
    {
        $record = AccessRequest::findDetailed((int) $id);

        if ($record === null) {
            return $this->abort(404, 'Access request not found.');
        }

        $user = current_user();

        if ($user === null || (!is_admin() && (int) $record['user_id'] !== (int) $user['id'])) {
            return $this->abort(403, 'Access denied.');
        }

        return $this->render('access-request', ['accessRequest' => $record]);
    }
}