<?php

declare(strict_types=1);

use GatewayAPI\Core\App;
use GatewayAPI\Core\Database;
use GatewayAPI\Core\Request;
use GatewayAPI\Core\Response;
use GatewayAPI\Core\Router;
use GatewayAPI\Services\AuthService;
use GatewayAPI\Services\I18nService;

error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$rootPath = dirname(__DIR__, 2);

require $rootPath . '/src/Core/helpers.php';

spl_autoload_register(static function (string $class) use ($rootPath): void {
    $prefix = 'GatewayAPI\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $filePath = $rootPath . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($filePath)) {
        require $filePath;
    }
});

$config = [
    'app' => require $rootPath . '/config/app.php',
    'database' => require $rootPath . '/config/database.php',
    'auth' => require $rootPath . '/config/auth.php',
];

date_default_timezone_set($config['app']['timezone']);

App::boot($rootPath, $config);
Database::connection();
I18nService::bootstrap();

$router = new Router();
$request = Request::capture();
AuthService::bootstrapRequestContext($request);

$registerRoutes = require $rootPath . '/routes/web.php';
$registerRoutes($router);

$response = $router->dispatch($request);

if (!$response instanceof Response) {
    $response = Response::html((string) $response);
}

$response->send();