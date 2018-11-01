<?php declare(strict_types=1);

namespace SsoPhp;

class Client
{
    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $clientToken;

    /**
     * @var string
     */
    protected $serverUrl;

    public function __construct(
        string $clientSecret,
        string $clientToken,
        string $serverUrl
    ) {
        $this->clientSecret = $clientSecret;
        $this->clientToken = $clientToken;
        $this->serverUrl = rtrim($serverUrl, "/");

        if (mb_substr($this->serverUrl, -1) !== "=") {
            $this->serverUrl .= "/";
        }
    }

    public function getServerUrl(): string
    {
        return $this->serverUrl;
    }

    public function connect(): array
    {
        return $this->makeRequest("GET", "connect");
    }

    public function register(string $username, string $password, array $context = []): array
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("POST", "register", [], [
            'authorization' => $authorization,
            'context' => $context,
        ]);

        return $response;
    }

    public function login(string $username, string $password): array
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("GET", "login", [
            "Authorization" => "Basic {$authorization}",
        ]);

        return $response;
    }

    public function validateToken(string $username, string $token): array
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest("GET", "validateToken", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    public function logout(string $username, string $token): array
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest("GET", "logout", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    public function generateRegisterUrl(): array
    {
        $response = $this->makeRequest("GET", "generateRegisterUrl");

        return $response;
    }

    public function generateLoginUrl(): array
    {
        $response = $this->makeRequest("GET", "generateLoginUrl");

        return $response;
    }

    protected function buildAuthorization(string $username, string $passwordOrToken): string
    {
        return base64_encode(sprintf(
            '%s:%s',
            $username,
            $passwordOrToken
        ));
    }

    protected function makeRequest(
        string $method,
        string $call,
        array $headers = [],
        array $postValues = []
    ): array {
        $url = $this->serverUrl . ltrim($call, "/");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postValues));
        }

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers["client_secret"] = $this->clientSecret;
        $headers["client_token"] = $this->clientToken;

        $headersBuilt = [];
        foreach ($headers as $key => $value) {
            $headersBuilt[] = "{$key}: {$value}";
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headersBuilt);

        $jsonResponse = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($jsonResponse, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($response)) {
            $response["call"] = $call;
        } else {
            $response = [
                "call" => $call,
                "error" => "Response was not valid",
                "response" => $jsonResponse
            ];
        }

        return $response;
    }
}
