<?php declare(strict_types=1);

namespace SsoPhp;

use SsoPhp\Server\ProviderInterface;

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
     * @var ProviderInterface
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

    public function connect(): array
    {
        if (!$this->provider->validateCredentials()) {
            return $this->errorResponseFromException(
                Exception::clientCredentialsInvalid()
            );
        }

        $metadata = $this->provider->getMetadataForContext(
            'connect',
            [
                'clientSecret' => $this->clientSecret,
                'clientToken' => $this->clientToken,
            ]
        );

        return $this->successResponse([
            'metadata' => $metadata,
        ]);
    }

    public function register(): array
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

        $metadata = $this->provider->getMetadataForContext(
            'login',
            [
                'username' => $username
            ]
        );

        return $this->successResponse([
            'metadata' => $metadata,
        ]);
    }

    public function login(): array
    {
        [$username, $password] = $this->parseAuthorization();

        if (!$this->provider->validateLogin($username, $password)) {
            return $this->errorResponseFromException(
                Exception::loginFailed()
            );
        }

        $token = $this->provider->generateToken($username);

        $metadata = $this->provider->getMetadataForContext(
            'login',
            [
                'username' => $username,
                'token' => $token
            ]
        );

        return $this->successResponse([
            'token' => $token,
            'metadata' => $metadata,
        ]);
    }

    public function validateToken(): array
    {
        [$username, $token] = $this->parseAuthorization();

        if (!$this->provider->validateToken($username, $token)) {
            return $this->errorResponseFromException(
                Exception::tokenValidationFailed()
            );
        }

        $metadata = $this->provider->getMetadataForContext(
            'validateToken',
            [
                'username' => $username,
                'token' => $token
            ]
        );

        return $this->successResponse([
            'metadata' => $metadata
        ]);
    }

    public function logout(): array
    {
        [$username, $token] = $this->parseAuthorization();

        if (!$this->provider->revokeToken($username, $token)) {
            return $this->errorResponseFromException(
                Exception::tokenRevocationFailed()
            );
        }

        $metadata = $this->provider->getMetadataForContext(
            'logout',
            [
                'username' => $username,
                'token' => $token
            ]
        );

        return $this->successResponse([
            'metadata' => $metadata,
        ]);
    }

    public function generateRegisterUrl(): array
    {
        $url = $this->provider->generateRegisterUrl();

        if (!$url) {
            return $this->errorResponseFromException(
                Exception::registerUrlGenerationFailed()
            );
        }

        $metadata = $this->provider->getMetadataForContext(
            'generateRegisterUrl',
            [
                'url' => $url
            ]
        );

        return $this->successResponse([
            'url' => $url,
            'metadata' => $metadata,
        ]);
    }

    public function generateLoginUrl(): array
    {
        $url = $this->provider->generateLoginUrl();

        if (!$url) {
            return $this->errorResponseFromException(
                Exception::loginUrlGenerationFailed()
            );
        }

        $metadata = $this->provider->getMetadataForContext(
            'generateLoginUrl',
            [
                'url' => $url
            ]
        );

        return $this->successResponse([
            'url' => $url,
            'metadata' => $metadata,
        ]);
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

    protected function successResponse(array $data = []): array
    {
        return [
            'status' => 'success',
            'data' => $data,
        ];
    }

    protected function errorResponse(string $message, int $code = null): array
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($code !== null) {
            $response['code'] = $code;
        }

        return $response;
    }

    protected function errorResponseFromException(\Exception $exception): array
    {
        return $this->errorResponse(
            $exception->getMessage(),
            $exception->getCode()
        );
    }
}
