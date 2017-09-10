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

    /**
     * @param string $clientSecret
     * @param string $clientToken
     * @param string $serverUrl
     */
    public function __construct(
        $clientSecret,
        $clientToken,
        $serverUrl
    ) {
        $this->clientSecret = $clientSecret;
        $this->clientToken  = $clientToken;
        $this->serverUrl    = rtrim($serverUrl, "/");

        if (substr($this->serverUrl, -1) !== "=") {
            $this->serverUrl .= "/";
        }
    }

    /**
     * @return array
     */
    public function connect()
    {
        return $this->makeRequest("connect");
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return array
     */
    public function login($username, $password)
    {
        $authorization = base64_encode("{$username}:{$password}");

        $response = $this->makeRequest("login", [
            "Authorization" => "Basic {$authorization}",
        ]);

        return $response;
    }

    /**
     * @param string $username
     * @param string $token
     *
     * @return array
     */
    public function logout($username, $token)
    {
        $authorization = base64_encode("{$username}:{$token}");

        $response = $this->makeRequest("logout", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    /**
     * @param string $username
     * @param string $token
     *
     * @return array
     */
    public function validateToken($username, $token)
    {
        $authorization = base64_encode("{$username}:{$token}");

        $response = $this->makeRequest("validateToken", [
            "Authorization" => "Bearer {$authorization}",
        ]);

        return $response;
    }

    /**
     * @return array
     */
    public function generateRegisterUrl()
    {
        $response = $this->makeRequest("generateRegisterUrl");
        return $response;
    }

    /**
     * @return array
     */
    public function generateLoginUrl()
    {
        $response = $this->makeRequest("generateLoginUrl");
        return $response;
    }

    /**
     * @param string $call
     * @param array  $headers
     *
     * @return array
     */
    protected function makeRequest($call, array $headers = [])
    {
        $url = $this->serverUrl . ltrim($call, "/");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers["client_secret"] = $this->clientSecret;
        $headers["client_token"]  = $this->clientToken;

        $headersBuilt = [];
        foreach ($headers as $key => $value) {
            $headersBuilt[] = "{$key}: {$value}";
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headersBuilt);

        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($response)) {
            return ["call" => $call, "error" => "Response was not valid"];
        }

        $response["call"] = $call;

        return $response;
    }
}
