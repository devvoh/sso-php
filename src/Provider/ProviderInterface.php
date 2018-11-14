<?php declare(strict_types=1);

namespace SsoPhp\Provider;

interface ProviderInterface
{
    public function setClientSecret(string $clientSecret): void;

    public function setClientToken(string $clientToken): void;

    public function validateCredentials(): bool;

    public function registerUser(string $username, string $password): bool;

    public function deleteUser(string $username): bool;

    public function loginUser(string $username, string $password): bool;

    public function validateToken(string $username, string $token): bool;

    public function revokeToken(string $username, string $token): bool;

    public function generateToken(string $username): string;

    public function getMetadataForCall(string $call, array $data): array;
}
