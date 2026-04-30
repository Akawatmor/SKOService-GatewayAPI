<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Models\AccessGrant;
use GatewayAPI\Models\AccessRequest;
use GatewayAPI\Models\ApiService;
use GatewayAPI\Models\RequestLog;
use GatewayAPI\Models\User;
use GatewayAPI\Services\ApiKeyService;

final class DashboardController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        $user = current_user();

        if ($user === null) {
            return $this->redirect('/login');
        }

        return $this->render('dashboard', [
            'user' => User::findWithRole((int) $user['id']) ?? $user,
            'grants' => AccessGrant::forUser((int) $user['id']),
            'requests' => AccessRequest::forUser((int) $user['id']),
            'usageLogs' => RequestLog::forUser((int) $user['id']),
            'managedApis' => ApiService::manageableListForUser($user),
            'generatedApiKey' => flash('generated_api_key'),
        ]);
    }

    public function regenerateApiKey(Request $request): \GatewayAPI\Core\Response
    {
        $user = current_user();

        if ($user === null) {
            return $this->redirect('/login');
        }

        if (!\GatewayAPI\Helpers\Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');

            return $this->redirect('/dashboard');
        }

        $apiKey = ApiKeyService::generate();
        User::updateById((int) $user['id'], [
            'api_key' => $apiKey['hash'],
            'api_key_prefix' => $apiKey['prefix'],
        ]);
        $_SESSION['auth']['user'] = User::findWithRole((int) $user['id']) ?? $_SESSION['auth']['user'];

        flash('success', 'API key regenerated.');
        flash('generated_api_key', $apiKey['plain']);

        return $this->redirect('/dashboard');
    }
}