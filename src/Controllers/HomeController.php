<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Models\ApiService;
use GatewayAPI\Models\User;

final class HomeController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        $stats = ApiService::stats();
        $support = [
            ['type' => 'REST', 'description' => 'HTTP JSON endpoints with proxy forwarding.'],
            ['type' => 'GraphQL', 'description' => 'Schema-driven query APIs with interactive console.'],
            ['type' => 'SOAP', 'description' => 'Legacy XML RPC support through gateway documentation.'],
            ['type' => 'WebSocket', 'description' => 'Realtime routing planned in hybrid gateway mode.'],
            ['type' => 'Webhook', 'description' => 'Inbound and relay-ready event integrations.'],
            ['type' => 'File', 'description' => 'Offline snapshot delivery via SQLite or binary artifacts.'],
        ];

        return $this->render('home', [
            'featuredApis' => ApiService::featured(),
            'supportMatrix' => $support,
            'stats' => [
                'total_apis' => $stats['total_apis'],
                'total_endpoints' => $stats['total_endpoints'],
                'developers' => User::developerCount(),
            ],
        ]);
    }
}