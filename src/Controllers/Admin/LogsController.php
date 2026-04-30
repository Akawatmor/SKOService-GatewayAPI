<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers\Admin;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Models\RequestLog;

final class LogsController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        return $this->render('admin/logs', [
            'logs' => RequestLog::recent(100),
        ], 'admin');
    }
}