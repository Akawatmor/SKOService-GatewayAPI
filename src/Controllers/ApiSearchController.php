<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Models\ApiService;

final class ApiSearchController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        $filters = [
            'search' => (string) $request->queryParam('search', ''),
            'api_type' => (string) $request->queryParam('api_type', ''),
            'standard' => (string) $request->queryParam('standard', ''),
            'status' => (string) $request->queryParam('status', ''),
            'visibility' => (string) $request->queryParam('visibility', ''),
            'sort' => (string) $request->queryParam('sort', 'newest'),
        ];
        $result = ApiService::searchCatalog($filters, current_user(), (int) $request->queryParam('page', 1));

        return $this->render('search', [
            'filters' => $filters,
            'apis' => $result['items'],
            'pagination' => $result['pagination'],
        ]);
    }
}