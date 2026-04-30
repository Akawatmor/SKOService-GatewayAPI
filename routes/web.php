<?php

declare(strict_types=1);

use GatewayAPI\Controllers\AccessRequestController;
use GatewayAPI\Controllers\Admin\AccessRequestManagerController;
use GatewayAPI\Controllers\Admin\ApiManagerController;
use GatewayAPI\Controllers\Admin\DashboardController as AdminDashboardController;
use GatewayAPI\Controllers\Admin\LogsController;
use GatewayAPI\Controllers\Admin\RateLimitController;
use GatewayAPI\Controllers\Admin\UserManagerController;
use GatewayAPI\Controllers\ApiDetailController;
use GatewayAPI\Controllers\ApiSearchController;
use GatewayAPI\Controllers\AuthController;
use GatewayAPI\Controllers\DashboardController;
use GatewayAPI\Controllers\HomeController;
use GatewayAPI\Controllers\ProxyController;
use GatewayAPI\Core\Router;
use GatewayAPI\Middleware\AuthMiddleware;
use GatewayAPI\Middleware\CorsMiddleware;
use GatewayAPI\Middleware\RateLimitMiddleware;
use GatewayAPI\Middleware\RbacMiddleware;

return static function (Router $router): void {
    $router->get('/', [new HomeController(), 'index']);
    $router->get('/search', [new ApiSearchController(), 'index']);
    $router->get('/api/{slug}', [new ApiDetailController(), 'show']);
    $router->get('/api/{slug}/snapshot/{file}', [new ApiDetailController(), 'downloadSnapshot'], [RateLimitMiddleware::class]);
    $router->post('/api/{slug}/try', [new ApiDetailController(), 'tryItOut'], [CorsMiddleware::class, RateLimitMiddleware::class]);
    $router->get('/api/{slug}/health', [new ApiDetailController(), 'healthCheck'], [CorsMiddleware::class, RateLimitMiddleware::class]);

    $router->get('/login', [new AuthController(), 'showLogin']);
    $router->post('/login', [new AuthController(), 'login'], [RateLimitMiddleware::class]);
    $router->get('/register', [new AuthController(), 'showRegister']);
    $router->post('/register', [new AuthController(), 'register'], [RateLimitMiddleware::class]);
    $router->post('/logout', [new AuthController(), 'logout'], [AuthMiddleware::class]);
    $router->post('/api/refresh', [new AuthController(), 'refreshToken'], [CorsMiddleware::class]);

    $router->get('/dashboard', [new DashboardController(), 'index'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/dashboard/regenerate-api-key', [new DashboardController(), 'regenerateApiKey'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/access-request', [new AccessRequestController(), 'store'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->get('/access-request/{id}', [new AccessRequestController(), 'show'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->get('/workspace/apis', [new ApiManagerController(), 'index'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->get('/workspace/apis/create', [new ApiManagerController(), 'create'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/workspace/apis', [new ApiManagerController(), 'store'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->get('/workspace/apis/{id}/edit', [new ApiManagerController(), 'edit'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/workspace/apis/{id}/update', [new ApiManagerController(), 'update'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/workspace/apis/{id}/delete', [new ApiManagerController(), 'delete'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/workspace/apis/{id}/endpoints', [new ApiManagerController(), 'storeEndpoint'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/workspace/apis/{id}/endpoints/{endpointId}/delete', [new ApiManagerController(), 'deleteEndpoint'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/workspace/apis/{id}/schema', [new ApiManagerController(), 'uploadSchema'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);
    $router->post('/workspace/apis/{id}/snapshots/generate', [new ApiManagerController(), 'generateSnapshot'], [AuthMiddleware::class, RbacMiddleware::class . ':developer']);

    $router->any('/proxy/{slug}/{path:.*}', [new ProxyController(), 'forward'], [CorsMiddleware::class, AuthMiddleware::class, RbacMiddleware::class . ':developer', RateLimitMiddleware::class]);

    $router->get('/admin', [new AdminDashboardController(), 'index'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/apis', [new ApiManagerController(), 'index'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/apis/create', [new ApiManagerController(), 'create'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/apis', [new ApiManagerController(), 'store'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/apis/{id}/edit', [new ApiManagerController(), 'edit'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/apis/{id}/endpoints', [new ApiManagerController(), 'edit'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/apis/{id}/schema', [new ApiManagerController(), 'edit'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/apis/{id}/snapshots', [new ApiManagerController(), 'edit'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/apis/{id}/update', [new ApiManagerController(), 'update'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/apis/{id}/delete', [new ApiManagerController(), 'delete'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/apis/{id}/endpoints', [new ApiManagerController(), 'storeEndpoint'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/apis/{id}/endpoints/{endpointId}/delete', [new ApiManagerController(), 'deleteEndpoint'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/apis/{id}/schema', [new ApiManagerController(), 'uploadSchema'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/apis/{id}/snapshots/generate', [new ApiManagerController(), 'generateSnapshot'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);

    $router->get('/admin/users', [new UserManagerController(), 'index'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/users/{id}/role', [new UserManagerController(), 'updateRole'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/access-requests', [new AccessRequestManagerController(), 'index'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/access-requests/{id}/review', [new AccessRequestManagerController(), 'review'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/rate-limits', [new RateLimitController(), 'index'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->post('/admin/rate-limits', [new RateLimitController(), 'save'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
    $router->get('/admin/logs', [new LogsController(), 'index'], [AuthMiddleware::class, RbacMiddleware::class . ':admin']);
};