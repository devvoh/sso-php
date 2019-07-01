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
        } elseif (substr($serverUrl, 0, 8) !== 'https://') {
            throw SsoException::secureServerUrlRequired();
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

    public function registerUser(string $username, string $password): Response
    {
        return $this->makeRequest("POST", "registerUser", [], [
            'authorization' => Authorization::buildAuthorization($username, $password),
        ]);
    }

    public function deleteUser(string $username): Response
    {
        return $this->makeRequest("POST", "deleteUser", [], [
            'username' => $username,
        ]);
    }

    public function loginUser(string $username, string $password): Response
    {
        return $this->makeRequest("POST", "loginUser", [
            "Authorization" => Authorization::buildBasicAuthorization($username, $password),
        ]);
    }

    public function validateToken(string $username, string $token): Response
    {
        return $this->makeRequest("GET", "validateToken", [
            "Authorization" => Authorization::buildBearerAuthorization($username, $token),
        ]);
    }

    public function revokeToken(string $username, string $token): Response
    {
        return $this->makeRequest("POST", "revokeToken", [
            "Authorization" => Authorization::buildBearerAuthorization($username, $token),
        ]);
    }

    public function registerUserWithContext(string $username, string $password, array $context = []): Response
    {
        $response = $this->makeRequest("POST", "registerUserWithContext", [], [
            'authorization' => Authorization::buildAuthorization($username, $password),
            'context' => $context,
        ]);

        return $response;
    }

    public function updateUserContext(string $username, array $context): Response
    {
        $response = $this->makeRequest(
            "POST",
            "updateUserContext",
            [],
            [
                'username' => $username,
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

    protected function makeRequest(
        string $method,
        string $call,
        array $headers = [],
        array $postValues = []
    ): Response {
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

        $headers["SsoPhp-Client-Secret"] = $this->clientSecret;
        $headers["SsoPhp-Client-Token"] = $this->clientToken;

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
