<?php

use SsoPhp\Server\ProviderInterface;

class ExampleProvider implements ProviderInterface
{
    /** @var string */
    protected $clientSecret;

    /** @var string */
    protected $clientToken;

    /** @var string */
    protected $tokenStorageFile = __DIR__ . "/token_storage.json";

    /** @var array */
    protected $tokenStorage = [];

    /** @var string */
    protected $accountEndpoint = "https://x.com/";

    public function __construct()
    {
        if (!file_exists($this->tokenStorageFile)) {
            @touch($this->tokenStorageFile);
        }

        $this->loadStorage();
    }

    public function __destruct()
    {
        $this->saveStorage();
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function setClientToken(string $clientToken): void
    {
        $this->clientToken = $clientToken;
    }

    public function validateCredentials(): bool
    {
        if ($this->clientSecret !== 'secret') {
            return false;
        }

        if ($this->clientToken !== 'client-token-goes-here') {
            return false;
        }

        return true;
    }

    public function validateLogin(string $username, string $password): bool
    {
        if ($username !== 'user' || $password !== 'pass') {
            return false;
        }

        return true;
    }

    public function validateToken(string $username, string $token): bool
    {
        if (!isset($this->tokenStorage[$username])) {
            return false;
        }

        if ($this->tokenStorage[$username] !== $token) {
            return false;
        }

        return true;
    }

    public function revokeToken(string $username, string $token): bool
    {
        if (!$this->validateToken($username, $token)) {
            return false;
        }

        unset($this->tokenStorage[$username]);

        return true;
    }

    public function generateToken(string $username): string
    {
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $token = substr_replace($token, uniqid(), 16, 0);

        $this->tokenStorage[$username] = $token;

        return $token;
    }

    public function generateRegisterUrl(): string
    {
        // Normally you'd generate a token differently and store it so your 'endpoint' pages can pick it up.
        // This is why we 'fake' the username to a uniqid, to use the test system's token storage.
        return sprintf(
            '%s/register/%s',
            $this->accountEndpoint,
            $this->generateToken("register_" . uniqid())
        );
    }

    public function generateLoginUrl(): string
    {
        // Normally you'd generate a token differently and store it so your 'endpoint' pages can pick it up.
        // This is why we 'fake' the username to a uniqid, to use the test system's token storage.
        return sprintf(
            '%s/login/%s',
            $this->accountEndpoint,
            $this->generateToken("login" . uniqid())
        );
    }

    public function getMetadataForContext(string $context, array $data): array
    {
        // It's possible to generate metadata for specific contexts here
        switch ($context) {
            case "login":
            case "validateToken":
                return ["username" => $data["username"]];
        }
        return [];
    }

    protected function loadStorage(): void
    {
        $storage = file_get_contents($this->tokenStorageFile);
        $this->tokenStorage = json_decode($storage, true);
    }

    protected function saveStorage(): void
    {
        $storage = json_encode($this->tokenStorage);
        file_put_contents($this->tokenStorageFile, $storage);
    }
}
