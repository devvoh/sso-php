<?php
namespace Test;

class ExampleProvider implements \SsoPhp\Server\ProviderInterface
{
    protected $tokenStorageFile = __DIR__ . "/token_storage.json";
    protected $tokenStorage = [];
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

    /**
     * @inheritdoc
     */
    public function validateCredentials($clientId, $clientToken)
    {
        if ($clientId !== "a78dg4") {
            return false;
        }
        if ($clientToken !== "client-token-goes-here") {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function validateLogin($username, $password)
    {
        if ($username !== "user") {
            return false;
        }
        if ($password !== "pass") {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function validateToken($username, $token)
    {
        if (!isset($this->tokenStorage[$username])) {
            return false;
        }
        if ($this->tokenStorage[$username] !== $token) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function revokeToken($username, $token)
    {
        if (!$this->validateToken($username, $token)) {
            return false;
        }

        unset($this->tokenStorage[$username]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function generateToken($username)
    {
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $token = substr_replace($token, uniqid(), 16, 0);

        $this->tokenStorage[$username] = $token;

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function generateRegisterUrl()
    {
        // Normally you'd generate a token differently and store it so your 'endpoint' pages can pick it up.
        // This is why we 'fake' the username to a uniqid, to use the test system's token storage.
        $token = $this->generateToken("register_" . uniqid());
        return "{$this->accountEndpoint}/register/{$token}";
    }

    /**
     * @inheritdoc
     */
    public function generateLoginUrl()
    {
        // Normally you'd generate a token differently and store it so your 'endpoint' pages can pick it up.
        // This is why we 'fake' the username to a uniqid, to use the test system's token storage.
        $token = $this->generateToken("login_" . uniqid());
        return "{$this->accountEndpoint}/login/{$token}";
    }

    public function loadStorage()
    {
        $storage = file_get_contents($this->tokenStorageFile);
        $this->tokenStorage = json_decode($storage, true);
    }

    public function saveStorage()
    {
        $storage = json_encode($this->tokenStorage);
        file_put_contents($this->tokenStorageFile, $storage);
    }
}
