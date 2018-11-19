<?php declare(strict_types=1);

namespace SsoPhp;

use SsoPhp\Exceptions\SsoException;
use SsoPhp\Response\ResponseStatus;

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
        if (defined('SSO_CLIENT_SECURE_MODE') && SSO_CLIENT_SECURE_MODE === false) {
            trigger_error('Disabling secure mode is highly discouraged. End-to-end encryption safeguards credentials.');
        } else {
            if (substr($serverUrl, 0, 8) !== 'https://') {
                throw SsoException::secureServerUrlRequired();
            }
        }

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

    public function connect(): Response
    {
        return $this->makeRequest("GET", "connect");
    }

    public function register(string $username, string $password): Response
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("POST", "register", [], [
            'authorization' => $authorization,
        ]);

        return $response;
    }

    public function deleteUser(string $username): Response
    {
        $response = $this->makeRequest("POST", "deleteUser", [], [
            'username' => $username,
        ]);

        return $response;
    }

    public function login(string $username, string $password): Response
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("POST", "login", [
            "Authorization" => "Basic {$authorization}",
        ]);

        return $response;
    }

    public function validateToken(string $username, string $token): Response
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest("GET", "validateToken", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    public function revokeToken(string $username, string $token): Response
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest("POST", "revokeToken", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    public function registerWithContext(string $username, string $password, array $context = []): Response
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("POST", "registerWithContext", [], [
            'authorization' => $authorization,
            'context' => $context,
        ]);

        return $response;
    }

    public function updateContext(string $username, string $token, array $context): Response
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest(
            "POST",
            "updateContext",
            [
                "Authorization" => "Bearer {$authorization}",
            ],
            [
                'context' => $context,
            ]
        );

        return $response;
    }

    public function generateRegisterUrl(): Response
    {
        $response = $this->makeRequest("GET", "generateRegisterUrl");

        return $response;
    }

    public function generateLoginUrl(): Response
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
    ): Response
    {
        $url = $this->serverUrl . ltrim($call, "/");

        $curlRequest = $this->createCurlRequest($url);

        if ($method === CurlRequest::METHOD_POST) {
            $curlRequest->setOptions([
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => http_build_query($postValues),
            ]);
        }

        $curlRequest->setOptions([
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        foreach ($this->options as $name => $value) {
            $curlRequest->setOption($name, $value);
        }

        $headers["client_secret"] = $this->clientSecret;
        $headers["client_token"] = $this->clientToken;

        $headersBuilt = [];
        foreach ($headers as $key => $value) {
            $headersBuilt[] = "{$key}: {$value}";
        }

        $curlRequest->setOption(CURLOPT_HTTPHEADER, $headersBuilt);

        $jsonResponse = $curlRequest->execute();

        if ($jsonResponse === false) {
            return new Response(
                ResponseStatus::STATUS_ERROR,
                [
                    'message' => 'Could not connect',
                    'code' => 0,
                ],
                $call
            );
        }

        $response = json_decode($jsonResponse, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($response)) {
            $response['call'] = $call;
        } else {
            return new Response(
                ResponseStatus::STATUS_ERROR,
                [
                    'message' => 'Response was not valid',
                    'code' => 0,
                    'response' => $jsonResponse,
                ],
                $call
            );
        }

        return Response::createFromArray($response);
    }

    protected function createCurlRequest(string $url): CurlRequest
    {
        return new CurlRequest($url);
    }
}
