<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers\Admin;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Helpers\Csrf;
use GatewayAPI\Models\ApiService;
use GatewayAPI\Models\RateLimit;
use GatewayAPI\Models\Role;

final class RateLimitController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        return $this->render('admin/rate-limit', [
            'rateLimits' => RateLimit::adminList(),
            'services' => ApiService::adminList(),
            'roles' => Role::all('id ASC'),
        ], 'admin');
    }

    public function save(Request $request): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect('/admin/rate-limits');
        }

        RateLimit::insert([
            'api_service_id' => $request->input('api_service_id') !== '' ? (int) $request->input('api_service_id') : null,
            'role_id' => $request->input('role_id') !== '' ? (int) $request->input('role_id') : null,
            'requests_per_minute' => (int) $request->input('requests_per_minute', 60),
            'requests_per_day' => (int) $request->input('requests_per_day', 1000),
        ]);

        flash('success', 'Rate limit rule added.');
        return $this->redirect('/admin/rate-limits');
    }
}