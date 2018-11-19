<?php

namespace SsoPhp\Tests;

use SsoPhp\Provider\ContextualProviderInterface;
use SsoPhp\Provider\ExternalProviderInterface;
use SsoPhp\Provider\ProviderInterface;

class TestProvider implements ProviderInterface, ContextualProviderInterface, ExternalProviderInterface
{
    /**
     * @var array
     */
    public $users = [
        'user' => ['password' => 'pass'],
    ];

    /**
     * @var array
     */
    public $tokens = [
        'user' => 'token',
    ];

    public function registerUserWithContext(string $username, string $password, array $context): bool
    {
        if (isset($this->users[$username])) {
            return false;
        }

        $this->users[$username] = [
            'password' => $password,
            'context' => $context,
        ];

        return true;
    }

    public function updateContext(string $username, array $context): bool
    {
        if (!isset($this->users[$username])) {
            return false;
        }

        $this->users[$username]['context'] = $context;

        return true;
    }

    public function generateRegisterUrl(): string
    {
        return 'https://server.test/register';
    }

    public function generateLoginUrl(): string
    {
        return 'https://server.test/login';
    }

    public function validateCredentials(string $clientSecret, string $clientToken): bool
    {
        return $clientSecret === 'secret' && $clientToken === 'token';
    }

    public function registerUser(string $username, string $password): bool
    {
        if (isset($this->users[$username])) {
            return false;
        }

        $this->users[$username] = [
            'password' => $password,
        ];

        return true;
    }

    public function deleteUser(string $username): bool
    {
        if (!isset($this->users[$username])) {
            return false;
        }

        unset($this->users[$username]);

        return true;
    }

    public function loginUser(string $username, string $password): bool
    {
        if (!isset($this->users[$username])) {
            return false;
        }

        return $this->users[$username]['password'] === $password;
    }

    public function validateToken(string $username, string $token): bool
    {
        if (!isset($this->users[$username])) {
            return false;
        }

        if (!isset($this->tokens[$username])) {
            return false;
        }

        if ($this->tokens[$username] !== $token) {
            return false;
        }

        return true;
    }

    public function revokeToken(string $username, string $token): bool
    {
        if (!isset($this->users[$username])) {
            return false;
        }

        if (!isset($this->tokens[$username])) {
            return false;
        }

        if ($this->tokens[$username] !== $token) {
            return false;
        }

        unset($this->tokens[$username]);

        return true;
    }

    public function generateToken(string $username): string
    {
        if (!isset($this->users[$username])) {
            return false;
        }

        $this->tokens[$username] = uniqid('', true);

        return $this->tokens[$username];
    }

    public function getMetadataForCall(string $call, array $data): array
    {
        return $data;
    }
}
