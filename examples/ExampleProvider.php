<?php

use SsoPhp\Provider\ProviderInterface;

class ExampleProvider implements ProviderInterface
{
    /** @var string */
    protected $clientSecret;

    /** @var string */
    protected $clientToken;

    /** @var string */
    protected $tokenStorageFile = __DIR__ . "/token_storage.json";

    /** @var string */
    protected $userStorageFile = __DIR__ . "/user_storage.json";

    /** @var array */
    protected $tokenStorage = [];

    /** @var array */
    protected $userStorage = [];

    /** @var string */
    protected $accountEndpoint = "https://x.com/";

    public function __construct()
    {
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

    public function registerUser(string $username, string $password, array $context): bool
    {
        $this->userStorage[$username] = [
            'password' => $password,
            'context' => $context,
        ];

        $this->saveStorage();

        return true;
    }

    public function loginUser(string $username, string $password): bool
    {
        $user = $this->userStorage[$username] ?? null;

        if ($user && $user['password'] === $password) {
            return true;
        }

        // we have a default login for example purposes
        if ($username !== 'user' || $password !== 'pass') {
            return false;
        }

        return true;
    }

    public function updateContext(string $username, array $context): bool
    {
        $this->userStorage[$username]['context'] = $context;

        return true;
    }

    public function validateToken(string $username, string $token): bool
    {
        $tokenFromStorage = $this->tokenStorage[$username] ?? null;

        return $tokenFromStorage == $token;
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

    public function getMetadataForCall(string $call, array $data): array
    {
        // It's possible to generate metadata for specific contexts here
        switch ($call) {
            case "login":
            case "logout":
            case "validateToken":
            case "register":
            case "updateContext":
                return [
                    "username" => $data["username"],
                    "context" => $this->userStorage[$data["username"]]['context'] ?? [],
                ];
        }
        return [];
    }

    protected function loadStorage(): void
    {
        $tokenStorage = @file_get_contents($this->tokenStorageFile);
        $this->tokenStorage = json_decode($tokenStorage, true) ?? [];

        $userStorage = @file_get_contents($this->userStorageFile);
        $this->userStorage = json_decode($userStorage, true) ?? [];
    }

    protected function saveStorage(): void
    {
        $tokenStorage = json_encode($this->tokenStorage);
        file_put_contents($this->tokenStorageFile, $tokenStorage);

        $userStorage = json_encode($this->userStorage);
        file_put_contents($this->userStorageFile, $userStorage);
    }
}
