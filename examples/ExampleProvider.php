<?php
namespace Test;

class ExampleProvider implements \SsoPhp\Server\ProviderInterface
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

    /**
     * @inheritdoc
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setClientToken($clientToken)
    {
        $this->clientToken = $clientToken;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validateCredentials()
    {
        if ($this->clientSecret !== "a78dg4") {
            return false;
        }
        if ($this->clientToken !== "client-token-goes-here") {
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

    public function getUserFromUsername($username)
    {
        // Normally you'd return a representation of the user here, for the external service to make use of.
        // This is useful to return at least a user id to make sure any links to a user account made externally
        // keep pointing to the same user, even if the username is ever changed. In this dirty filesystem storage,
        // we of course don't have proper ids, so we just return the username in an array.
        return ["username" => $username];
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
