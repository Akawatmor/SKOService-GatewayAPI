<?php

declare(strict_types=1);

namespace GatewayAPI\Helpers;

final class Validator
{
    /**
     * @return array<int, string>
     */
    public static function validateRegister(array $payload): array
    {
        $errors = [];
        $username = Sanitizer::string($payload['username'] ?? '');
        $email = Sanitizer::string($payload['email'] ?? '');
        $password = (string) ($payload['password'] ?? '');
        $confirm = (string) ($payload['confirm_password'] ?? '');

        if ($username === '') {
            $errors[] = 'Username is required.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $password)) {
            $errors[] = 'Password must be at least 8 characters and include upper, lower, number, and symbol.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Password confirmation does not match.';
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    public static function validateLogin(array $payload): array
    {
        $errors = [];

        if (!filter_var(Sanitizer::string($payload['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if (Sanitizer::string($payload['password'] ?? '') === '') {
            $errors[] = 'Password is required.';
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    public static function validateApiService(array $payload): array
    {
        $errors = [];

        foreach (['name', 'slug', 'mode', 'api_type', 'version', 'status'] as $field) {
            if (Sanitizer::string($payload[$field] ?? '') === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        $baseUrl = Sanitizer::string($payload['base_url'] ?? '');

        if ($baseUrl !== '' && !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Base URL must be a valid URL.';
        }

        return $errors;
    }
}