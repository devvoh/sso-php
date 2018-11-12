<?php declare(strict_types=1);

namespace SsoPhp\Server;

interface ProviderInterface
{
    public function setClientSecret(string $clientSecret): void;

    public function setClientToken(string $clientToken): void;

    public function validateCredentials(): bool;

    public function validateToken(string $username, string $token): bool;

    public function registerUser(string $username, string $password, array $context): bool;

    public function validateLogin(string $username, string $password): bool;

    public function revokeToken(string $username, string $token): bool;

    public function generateToken(string $username): string;

    public function getMetadataForContext(string $context, array $data): array;
}
