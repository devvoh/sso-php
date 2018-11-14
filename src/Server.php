<?php declare(strict_types=1);

namespace SsoPhp;

use SsoPhp\Provider\ExternalProviderInterface;
use SsoPhp\Provider\ProviderInterface;

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
     * @var ProviderInterface|ExternalProviderInterface
     */
    protected $provider;

    /**
     * @var string[]
     */
    protected $headers = [];

    public function __construct(
        string $clientSecret,
        string $clientToken,
        ProviderInterface $provider
    ) {
        $this->clientSecret = $clientSecret;
        $this->clientToken = $clientToken;
        $this->provider = $provider;

        $this->headers = getallheaders();

        $this->provider->setClientSecret($clientSecret);
        $this->provider->setClientToken($clientToken);
    }

    public function connect(): SsoResponse
    {
        if (!$this->provider->validateCredentials()) {
            return $this->errorResponseFromException(
                Exception::clientCredentialsInvalid()
            );
        }

        $metadata = $this->provider->getMetadataForCall(
            'connect',
            [
                'clientSecret' => $this->clientSecret,
                'clientToken' => $this->clientToken,
            ]
        );

        return $this->successResponse(
            'connect',
            [
                'metadata' => $metadata,
            ]
        );
    }

    public function register(): SsoResponse
    {
        $authorization = $_POST['authorization'] ?? null;

        if ($authorization === null) {
            return $this->errorResponseFromException(
                Exception::registerFailed()
            );
        }

        [$username, $password] = $this->parseAuthorization($authorization);

        $context = $_POST['context'] ?? [];

        if (!$this->provider->registerUser($username, $password, $context)) {
            return $this->errorResponseFromException(
                Exception::registerFailed()
            );
        }

        $metadata = $this->provider->getMetadataForCall(
            'register',
            [
                'username' => $username
            ]
        );

        return $this->successResponse(
            'register',
            [
                'metadata' => $metadata,
            ]
        );
    }

    public function login(): SsoResponse
    {
        [$username, $password] = $this->parseAuthorization();

        if (!$this->provider->loginUser($username, $password)) {
            return $this->errorResponseFromException(
                Exception::loginFailed()
            );
        }

        $token = $this->provider->generateToken($username);

        $metadata = $this->provider->getMetadataForCall(
            'login',
            [
                'username' => $username,
            ]
        );

        return $this->successResponse(
            'login',
            [
                'token' => $token,
                'metadata' => $metadata,
            ]
        );
    }

    public function updateContext(): SsoResponse
    {
        [$username, $token] = $this->parseAuthorization($authorization);

        $context = $_POST['context'] ?? [];

        if (!$this->provider->validateToken($username, $token)) {
            return $this->errorResponseFromException(
                Exception::updateContextFailed()
            );
        }

        if (!$this->provider->updateContext($username, $context)) {
            return $this->errorResponseFromException(
                Exception::updateContextFailed()
            );
        }

        $metadata = $this->provider->getMetadataForCall(
            'updateContext',
            [
                'username' => $username
            ]
        );

        return $this->successResponse(
            'updateContext',
            [
                'metadata' => $metadata,
            ]
        );
    }

    public function validateToken(): SsoResponse
    {
        [$username, $token] = $this->parseAuthorization();

        if (!$this->provider->validateToken($username, $token)) {
            return $this->errorResponseFromException(
                Exception::tokenValidationFailed()
            );
        }

        $metadata = $this->provider->getMetadataForCall(
            'validateToken',
            [
                'username' => $username,
            ]
        );

        return $this->successResponse(
            'validateToken',
            [
                'token' => $token,
                'metadata' => $metadata
            ]
        );
    }

    public function logout(): SsoResponse
    {
        [$username, $token] = $this->parseAuthorization();

        if (!$this->provider->revokeToken($username, $token)) {
            return $this->errorResponseFromException(
                Exception::tokenRevocationFailed()
            );
        }

        $metadata = $this->provider->getMetadataForCall(
            'logout',
            [
                'username' => $username,
            ]
        );

        return $this->successResponse(
            'logout',
            [
                'metadata' => $metadata,
            ]
        );
    }

    public function generateLoginUrl(): SsoResponse
    {
        if (!($this->provider instanceof ExternalProviderInterface)) {
            return $this->errorResponseFromException(
                Exception::loginUrlGenerationNotSupported()
            );
        }

        $url = $this->provider->generateLoginUrl();

        if (!$url) {
            return $this->errorResponseFromException(
                Exception::loginUrlGenerationFailed()
            );
        }

        $metadata = $this->provider->getMetadataForCall(
            'generateLoginUrl',
            [
                'url' => $url
            ]
        );

        return $this->successResponse(
            'generateLoginUrl',
            [
                'url' => $url,
                'metadata' => $metadata,
            ]
        );
    }

    public function generateRegisterUrl(): SsoResponse
    {
        if (!($this->provider instanceof ExternalProviderInterface)) {
            return $this->errorResponseFromException(
                Exception::registerUrlGenerationNotSupported()
            );
        }

        $url = $this->provider->generateRegisterUrl();

        if (!$url) {
            return $this->errorResponseFromException(
                Exception::registerUrlGenerationFailed()
            );
        }

        $metadata = $this->provider->getMetadataForCall(
            'generateRegisterUrl',
            [
                'url' => $url
            ]
        );

        return $this->successResponse(
            'generateRegisterUrl',
            [
                'url' => $url,
                'metadata' => $metadata,
            ]
        );
    }

    protected function parseAuthorization(string $authorization = null): array
    {
        if ($authorization === null) {
            try {
                $authorization = $this->getAuthorizationFromHeader();
            } catch (Exception $e) {
                return ['', ''];
            }
        }

        if (mb_stripos($authorization, 'Basic ') !== false) {
            $authorization = str_ireplace('Basic ', '', $authorization);
        } elseif (stripos($authorization, 'Bearer ') !== false) {
            $authorization = str_ireplace('Bearer ', '', $authorization);
        }

        $authorizationDecoded = base64_decode($authorization);

        $authorizationParts = explode(':', $authorizationDecoded);
        if (count($authorizationParts) !== 2) {
            return ['', ''];
        }

        return $authorizationParts;
    }

    /**
     * @throws Exception
     */
    protected function getAuthorizationFromHeader(): string
    {
        $authorization = $this->headers['Authorization'] ?? null;

        if ($authorization === null) {
            throw Exception::noAuthorizationHeader();
        }

        return $authorization;
    }

    protected function successResponse(string $call, array $data = []): SsoResponse
    {
        return new SsoResponse(
            $call,
            StatusTypes::STATUS_SUCCESS,
            $data
        );
    }

    protected function errorResponse(string $call, string $message, int $code = null): SsoResponse
    {
        $data = [
            'message' => $message,
        ];

        if ($code !== null) {
            $data['code'] = $code;
        }

        return new SsoResponse(
            $call,
            StatusTypes::STATUS_ERROR,
            $data
        );
    }

    protected function errorResponseFromException(\Exception $exception): SsoResponse
    {
        if ($exception instanceof Exception) {
            $call = $exception->getCall();
        }

        return $this->errorResponse(
            $call ?? 'none',
            $exception->getMessage(),
            $exception->getCode()
        );
    }
}
