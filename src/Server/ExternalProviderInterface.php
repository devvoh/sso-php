<?php declare(strict_types=1);

namespace SsoPhp\Server;

interface ExternalProviderInterface
{
    public function generateRegisterUrl(): string;

    public function generateLoginUrl(): string;
}
