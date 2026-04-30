<?php

declare(strict_types=1);

namespace GatewayAPI\Services;

use GatewayAPI\Core\App;
use GatewayAPI\Core\Request;
use GatewayAPI\Models\User;

final class AuthService
{
    /**
     * @var array<string, mixed>|null
     */
    private static ?array $currentUser = null;

    private static ?string $authMode = null;

    public static function bootstrapRequestContext(Request $request): void
    {
        self::$currentUser = null;
        self::$authMode = null;

        $user = null;

        $bearerToken = $request->bearerToken() ?: $request->cookie('gatewayapi_access_token');

        if ($bearerToken !== null) {
            $payload = JwtService::decode($bearerToken);
            if (is_array($payload) && isset($payload['sub'])) {
                $user = User::findWithRole((int) $payload['sub']);
                self::$authMode = 'jwt';
            }
        }

        if ($user === null) {
            $apiKey = $request->header('x-api-key');

            if ($apiKey !== null && $apiKey !== '') {
                $user = User::findByApiKeyHash(hash('sha256', $apiKey));
                self::$authMode = 'api_key';
            }
        }

        if ($user === null) {
            $refreshToken = $request->cookie('gatewayapi_refresh_token');

            if (is_string($refreshToken) && $refreshToken !== '') {
                $user = self::userByRefreshToken($refreshToken);

                if ($user !== null) {
                    self::persistUser($user, true);
                    self::$authMode = 'refresh';
                }
            }
        }

        if ($user === null && isset($_SESSION['auth']['user']['id'])) {
            $user = User::findWithRole((int) $_SESSION['auth']['user']['id']);
            self::$authMode = 'session';
        }

        if ($user !== null && (int) ($user['is_active'] ?? 0) === 1) {
            self::$currentUser = $user;
            $_SESSION['auth']['user'] = $user;
        } else {
            unset($_SESSION['auth']);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function currentUser(): ?array
    {
        return self::$currentUser;
    }

    public static function authMode(): ?string
    {
        return self::$authMode;
    }

    /**
     * @return array<string, string>|null
     */
    public static function attemptLogin(string $email, string $password): ?array
    {
        $user = User::findByEmail($email);

        if ($user === null || (int) ($user['is_active'] ?? 0) !== 1) {
            return null;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            return null;
        }

        $user = User::findWithRole((int) $user['id']);

        if ($user === null) {
            return null;
        }

        self::persistUser($user, true);
        User::updateById((int) $user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return [
            'access_token' => $_SESSION['auth']['access_token'],
            'refresh_token' => $GLOBALS['_COOKIE']['gatewayapi_refresh_token'] ?? '',
        ];
    }

    /**
     * @return array<string, string>|null
     */
    public static function refreshFromCookie(?string $token): ?array
    {
        if ($token === null || $token === '') {
            return null;
        }

        $user = self::userByRefreshToken($token);

        if ($user === null) {
            return null;
        }

        self::persistUser($user, true);

        return [
            'access_token' => $_SESSION['auth']['access_token'],
            'refresh_token' => $GLOBALS['_COOKIE']['gatewayapi_refresh_token'] ?? '',
        ];
    }

    public static function logout(): void
    {
        $user = self::$currentUser;

        if ($user !== null) {
            User::updateById((int) $user['id'], ['refresh_token' => null]);
        }

        self::$currentUser = null;
        self::$authMode = null;

        unset($_SESSION['auth']);

        setcookie('gatewayapi_access_token', '', time() - 3600, '/');
        setcookie('gatewayapi_refresh_token', '', time() - 3600, '/');
    }

    /**
     * @return array<string, mixed>
     */
    public static function issueApiKey(): array
    {
        return ApiKeyService::generate();
    }

    public static function persistUser(array $user, bool $rotateRefresh = false): void
    {
        $accessToken = JwtService::encode(['sub' => (int) $user['id']], (int) App::config('auth.jwt_ttl', 3600));
        $_SESSION['auth']['user'] = $user;
        $_SESSION['auth']['access_token'] = $accessToken;
        self::$currentUser = $user;

        setcookie('gatewayapi_access_token', $accessToken, [
            'expires' => time() + (int) App::config('auth.jwt_ttl', 3600),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        if ($rotateRefresh) {
            $plainRefresh = bin2hex(random_bytes(32));
            User::updateRefreshToken((int) $user['id'], hash('sha256', $plainRefresh));
            $GLOBALS['_COOKIE']['gatewayapi_refresh_token'] = $plainRefresh;

            setcookie('gatewayapi_refresh_token', $plainRefresh, [
                'expires' => time() + (int) App::config('auth.refresh_ttl', 604800),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function userByRefreshToken(string $plainToken): ?array
    {
        $hash = hash('sha256', $plainToken);
        $user = User::firstWhere(['refresh_token' => $hash]);

        return $user !== null ? User::findWithRole((int) $user['id']) : null;
    }
}