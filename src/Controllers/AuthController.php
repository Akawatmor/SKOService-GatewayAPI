<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Helpers\Csrf;
use GatewayAPI\Helpers\Sanitizer;
use GatewayAPI\Helpers\Validator;
use GatewayAPI\Models\Role;
use GatewayAPI\Models\User;
use GatewayAPI\Services\AuthService;

final class AuthController extends Controller
{
    public function showLogin(Request $request): \GatewayAPI\Core\Response
    {
        return $this->render('login');
    }

    public function login(Request $request): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');

            return $this->redirect('/login');
        }

        $payload = [
            'email' => Sanitizer::string($request->input('email', '')),
            'password' => (string) $request->input('password', ''),
        ];
        remember_old(['email' => $payload['email']]);
        $errors = Validator::validateLogin($payload);

        if ($errors !== []) {
            flash('error', implode(' ', $errors));

            return $this->redirect('/login');
        }

        $tokens = AuthService::attemptLogin($payload['email'], $payload['password']);

        if ($tokens === null) {
            flash('error', 'Invalid email or password.');

            return $this->redirect('/login');
        }

        forget_old();
        flash('success', 'Logged in successfully.');

        return $this->redirect('/dashboard');
    }

    public function showRegister(Request $request): \GatewayAPI\Core\Response
    {
        return $this->render('register');
    }

    public function register(Request $request): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');

            return $this->redirect('/register');
        }

        $payload = [
            'username' => Sanitizer::string($request->input('username', '')),
            'email' => Sanitizer::string($request->input('email', '')),
            'password' => (string) $request->input('password', ''),
            'confirm_password' => (string) $request->input('confirm_password', ''),
        ];
        remember_old($payload);
        $errors = Validator::validateRegister($payload);

        if (User::findByEmail($payload['email']) !== null) {
            $errors[] = 'Email already exists.';
        }

        if (User::firstWhere(['username' => $payload['username']]) !== null) {
            $errors[] = 'Username already exists.';
        }

        if ($errors !== []) {
            flash('error', implode(' ', $errors));

            return $this->redirect('/register');
        }

        $role = Role::findByName('developer');
        $apiKey = AuthService::issueApiKey();

        $userId = User::insert([
            'username' => $payload['username'],
            'email' => $payload['email'],
            'password_hash' => password_hash($payload['password'], PASSWORD_BCRYPT, ['cost' => (int) app_config('auth.bcrypt_cost', 12)]),
            'role_id' => $role['id'] ?? 2,
            'api_key' => $apiKey['hash'],
            'api_key_prefix' => $apiKey['prefix'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $user = User::findWithRole($userId);

        if ($user !== null) {
            AuthService::persistUser($user, true);
        }

        forget_old();
        flash('success', 'Registration complete.');
        flash('generated_api_key', $apiKey['plain']);

        return $this->redirect('/dashboard');
    }

    public function logout(Request $request): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');

            return $this->redirect('/dashboard');
        }

        AuthService::logout();
        flash('success', 'Logged out successfully.');

        return $this->redirect('/');
    }

    public function refreshToken(Request $request): \GatewayAPI\Core\Response
    {
        $tokens = AuthService::refreshFromCookie($request->cookie('gatewayapi_refresh_token'));

        if ($tokens === null) {
            return $this->json(['message' => 'Refresh token invalid or expired.'], 401);
        }

        return $this->json([
            'message' => 'Token refreshed.',
            'access_token' => $tokens['access_token'],
        ]);
    }
}