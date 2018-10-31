<?php

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
        return $this->makeRequest("connect");
    }

    public function login(string $username, string $password): array
    {
        $authorization = $this->buildAuthorization($username, $password);

        $response = $this->makeRequest("login", [
            "Authorization" => "Basic {$authorization}",
        ]);

        return $response;
    }

    public function logout(string $username, string $token): array
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest("logout", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    public function validateToken(string $username, string $token): array
    {
        $authorization = $this->buildAuthorization($username, $token);

        $response = $this->makeRequest("validateToken", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    public function generateRegisterUrl(): array
    {
        $response = $this->makeRequest("generateRegisterUrl");

        return $response;
    }

    public function generateLoginUrl(): array
    {
        $response = $this->makeRequest("generateLoginUrl");

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

    protected function makeRequest(string $call, array $headers = []): array
    {
        $url = $this->serverUrl . ltrim($call, "/");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
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

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($response)) {
            return ["call" => $call, "error" => "Response was not valid", "response" => $jsonResponse];
        }

        $response["call"] = $call;

        return $response;
    }
}
