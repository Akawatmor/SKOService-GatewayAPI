<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers\Admin;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Models\AccessRequest;
use GatewayAPI\Models\ApiService;
use GatewayAPI\Models\RequestLog;
use GatewayAPI\Models\User;

final class DashboardController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        return $this->render('admin/dashboard', [
            'stats' => [
                ...ApiService::stats(),
                'total_users' => User::countAll(),
            ],
            'pendingRequests' => AccessRequest::pendingList(),
            'recentLogs' => RequestLog::recent(10),
        ], 'admin');
    }
}