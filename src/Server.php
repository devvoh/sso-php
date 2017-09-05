<?php
namespace SsoPhp;

class Server
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientToken;

    /**
     * @var null|\SsoPhp\Server\ProviderInterface
     */
    protected $provider;

    /**
     * @param string                           $clientId
     * @param string                           $clientToken
     * @param \SsoPhp\Server\ProviderInterface $provider
     *
     * @throws \SsoPhp\Exception
     */
    public function __construct(
        $clientId,
        $clientToken,
        \SsoPhp\Server\ProviderInterface $provider
    ) {
        $this->clientId      = $clientId;
        $this->clientToken   = $clientToken;
        $this->provider      = $provider;

        if (!$this->provider->validateCredentials($this->clientId, $this->clientToken)) {
            throw \SsoPhp\Exception::clientCredentialsInvalid();
        }
    }

    /**
     * @return array
     */
    public function connect()
    {
        if (!$this->provider->validateCredentials($this->clientId, $this->clientToken)) {
            return $this->errorResponseFromException(\SsoPhp\Exception::clientCredentialsInvalid());
        }

        return $this->successResponse();
    }

    /**
     * @return array
     */
    public function generateLoginUrl()
    {
        $url = $this->provider->generateLoginUrl();
        if (!$url) {
            return $this->errorResponseFromException(\SsoPhp\Exception::loginUrlGenerationFailed());
        }

        return $this->successResponse([
            "url" => $url
        ]);
    }

    /**
     * @return array
     */
    public function generateRegisterUrl()
    {
        $url = $this->provider->generateRegisterUrl();
        if (!$url) {
            return $this->errorResponseFromException(\SsoPhp\Exception::registerUrlGenerationFailed());
        }

        return $this->successResponse([
            "url" => $url
        ]);
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return array
     */
    public function login($username, $password)
    {
        if (!$this->provider->validateLogin($username, $password)) {
            return $this->errorResponseFromException(\SsoPhp\Exception::loginFailed());
        }

        return $this->successResponse([
            "token" => $this->provider->generateToken($username)
        ]);
    }

    /**
     * @param string $authorization
     *
     * @return array
     */
    public function parseAuthorizationHeader($authorization)
    {
        if (stripos($authorization, "Basic ") !== false) {
            $authorization = str_ireplace("Basic ", "", $authorization);
        } elseif (stripos($authorization, "Bearer ") !== false) {
            $authorization = str_ireplace("Bearer ", "", $authorization);
        }

        $authorizationDecoded = base64_decode($authorization);
        if (strlen($authorizationDecoded) !== mb_strlen($authorizationDecoded)) {
            return $this->errorResponse(\SsoPhp\Exception::invalidAuthorizationHeader());
        }

        $authorizationParts = explode(":", $authorizationDecoded);
        if (count($authorizationParts) !== 2) {
            return $this->errorResponse(\SsoPhp\Exception::invalidAuthorizationHeader());
        }

        return [
            "username" => $authorizationParts[0],
            "password" => $authorizationParts[1],
        ];
    }

    /**
     * @param string $username
     * @param string $token
     *
     * @return array
     */
    public function logout($username, $token)
    {
        if (!$this->provider->revokeToken($username, $token)) {
            return $this->errorResponseFromException(\SsoPhp\Exception::tokenRevocationFailed());
        }

        return $this->successResponse();
    }

    /**
     * @param string $username
     * @param string $token
     *
     * @return array
     */
    public function validateToken($username, $token)
    {
        if (!$this->provider->validateToken($username, $token)) {
            return $this->errorResponseFromException(\SsoPhp\Exception::tokenValidationFailed());
        }

        return $this->successResponse();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function successResponse(array $data = null)
    {
        $response = [
            "status" => "success",
            "data"   => $data,
        ];
        return $response;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function failResponse(array $data)
    {
        $response = [
            "status" => "fail",
            "data"   => $data,
        ];
        return $response;
    }

    /**
     * @param \Exception $exception
     *
     * @return array
     */
    protected function errorResponseFromException(\Exception $exception)
    {
        return $this->errorResponse(
            $exception->getMessage(),
            $exception->getCode()
        );
    }

    /**
     * @param string   $message
     * @param int|null $code
     *
     * @return array
     */
    protected function errorResponse($message, $code = null)
    {
        $response = [
            "status"  => "error",
            "message" => $message,
        ];
        if ($code) {
            $response["code"] = $code;
        }
        return $response;
    }
}
