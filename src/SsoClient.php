<?php declare(strict_types=1);

namespace SsoPhp;

class SsoClient
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

    /**
     * @var array
     */
    protected $options = [
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ];

    public function __construct(
        string $clientSecret,
        string $clientToken,
        string $serverUrl,
        array $options = []
    ) {
        $this->clientSecret = $clientSecret;
        $this->clientToken = $clientToken;
        $this->serverUrl = rtrim($serverUrl, "/");

        if (mb_substr($this->serverUrl, -1) !== "=") {
            $this->serverUrl .= "/";
        }

        foreach ($options as $option => $value) {
            $this->options[$option] = $value;
        }
    }

    public function getServerUrl(): string
    {
        return $this->serverUrl;
    }

    public function connect(): SsoResponse
    {
        return $this->makeRequest("GET", "connect");
    }

    public function register(string $username, string $password): SsoResponse
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("POST", "register", [], [
            'authorization' => $authorization,
        ]);

        return $response;
    }

    public function deleteUser(string $username): SsoResponse
    {
        $response = $this->makeRequest("POST", "deleteUser", [], [
            'username' => $username,
        ]);

        return $response;
    }

    public function login(string $username, string $password): SsoResponse
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("POST", "login", [
            "Authorization" => "Basic {$authorization}",
        ]);

        return $response;
    }

    public function registerWithContext(string $username, string $password, array $context = []): SsoResponse
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("POST", "registerWithContext", [], [
            'authorization' => $authorization,
            'context' => $context,
        ]);

        return $response;
    }

    public function updateContext(string $username, string $token, array $context): SsoResponse
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest(
            "POST",
            "updateContext",
            [
                "Authorization" => "Basic {$authorization}",
            ],
            [
                'context' => $context,
            ]
        );

        return $response;
    }

    public function validateToken(string $username, string $token): SsoResponse
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest("GET", "validateToken", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    public function logout(string $username, string $token): SsoResponse
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest("POST", "logout", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    public function generateRegisterUrl(): SsoResponse
    {
        $response = $this->makeRequest("GET", "generateRegisterUrl");

        return $response;
    }

    public function generateLoginUrl(): SsoResponse
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
    ): SsoResponse {
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

        foreach ($this->options as $curlopt => $value) {
            curl_setopt($curl, $curlopt, $value);
        }

        $headers["client_secret"] = $this->clientSecret;
        $headers["client_token"] = $this->clientToken;

        $headersBuilt = [];
        foreach ($headers as $key => $value) {
            $headersBuilt[] = "{$key}: {$value}";
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headersBuilt);

        $jsonResponse = curl_exec($curl);
        curl_close($curl);

        if ($jsonResponse === false) {
            return new SsoResponse(
                $call,
                ResponseStatusTypes::STATUS_ERROR,
                [
                    'message' => 'Could not connect',
                    'code' => 0,
                ]
            );
        }

        $response = json_decode($jsonResponse, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($response)) {
            $response['call'] = $call;
        } else {
            return new SsoResponse(
                $call,
                ResponseStatusTypes::STATUS_ERROR,
                [
                    'message' => 'Response was not valid',
                    'code' => 0,
                    'response' => $jsonResponse,
                ]
            );
        }

        return SsoResponse::createFromArray($response);
    }
}
