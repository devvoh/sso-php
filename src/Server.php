<?php
namespace SsoPhp;

class Server
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
     * @var null|\SsoPhp\Server\ProviderInterface
     */
    protected $provider;

    /**
     * @param string                           $clientSecret
     * @param string                           $clientToken
     * @param \SsoPhp\Server\ProviderInterface $provider
     *
     * @throws \SsoPhp\Exception
     */
    public function __construct(
        $clientSecret,
        $clientToken,
        \SsoPhp\Server\ProviderInterface $provider
    ) {
        $this->clientSecret = $clientSecret;
        $this->clientToken  = $clientToken;
        $this->provider     = $provider;

        $this->provider->setClientSecret($clientSecret);
        $this->provider->setClientToken($clientToken);

        if (!$this->provider->validateCredentials()) {
            return $this->errorResponseFromException(\SsoPhp\Exception::clientCredentialsInvalid());
        }
    }

    /**
     * @return array
     */
    public function connect()
    {
        if (!$this->provider->validateCredentials()) {
            return $this->errorResponseFromException(\SsoPhp\Exception::clientCredentialsInvalid());
        }

        $metadata = $this->provider->getMetadataForContext("connect", [
            "clientSecret" => $this->clientSecret,
            "clientToken"  => $this->clientToken,
        ]);

        return $this->successResponse([
            "metadata" => $metadata,
        ]);
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

        $metadata = $this->provider->getMetadataForContext("validateToken", [
            "username" => $username,
            "token"    => $token
        ]);

        return $this->successResponse([
            "metadata" => $metadata
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

        $token = $this->provider->generateToken($username);

        $metadata = $this->provider->getMetadataForContext("login", [
            "username" => $username,
            "token"    => $token
        ]);

        return $this->successResponse([
            "token"    => $token,
            "metadata" => $metadata,
        ]);
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

        $metadata = $this->provider->getMetadataForContext("logout", [
            "username" => $username,
            "token"    => $token
        ]);

        return $this->successResponse([
            "metadata" => $metadata,
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

        $metadata = $this->provider->getMetadataForContext("generateRegisterUrl", [
            "url" => $url
        ]);

        return $this->successResponse([
            "url"      => $url,
            "metadata" => $metadata,
        ]);
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

        $metadata = $this->provider->getMetadataForContext("generateLoginUrl", [
            "url" => $url
        ]);

        return $this->successResponse([
            "url"      => $url,
            "metadata" => $metadata,
        ]);
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
}
