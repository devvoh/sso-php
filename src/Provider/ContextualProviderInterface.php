<?php declare(strict_types=1);

namespace SsoPhp\Provider;

interface ContextualProviderInterface
{
    public function registerUserWithContext(string $username, string $password, array $context): bool;

    public function updateContext(string $username, array $context): bool;
}
