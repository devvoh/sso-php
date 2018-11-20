<?php declare(strict_types=1);

namespace SsoPhp;

use SsoPhp\Exceptions\AbstractException;
use SsoPhp\Exceptions\ContextualSsoException;
use SsoPhp\Exceptions\ExternalSsoException;
use SsoPhp\Exceptions\SsoException;
use SsoPhp\Provider\ContextualProviderInterface;
use SsoPhp\Provider\ExternalProviderInterface;
use SsoPhp\Provider\ProviderInterface;
use SsoPhp\Response\ResponseStatus;

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
     * @var ProviderInterface|ExternalProviderInterface|ContextualProviderInterface
     */
    protected $provider;

    /**
     * @var string[]
     */
    protected $headers = [];

    public function __construct(
        ProviderInterface $provider
    ) {
        $this->provider = $provider;

        $this->headers = getallheaders();

        $this->clientSecret = $this->headers['Sso-Php-Client-Secret'] ?? null;
        $this->clientToken = $this->headers['Sso-Php-Client-Token'] ?? null;
    }

    public function connect(): Response
    {
        try {
            $this->validateCredentials();
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'connect',
            [],
            $this->provider->getMetadataForCall(
                'connect',
                [
                    'clientSecret' => $this->clientSecret,
                    'clientToken' => $this->clientToken,
                ]
            )
        );
    }

    public function register(): Response
    {
        try {
            $this->validateCredentials();

            /** @var string|null $authorization */
            $authorization = $_POST['authorization'] ?? null;

            if ($authorization === null) {
                throw SsoException::noAuthorizationHeader();
            }

            [$username, $password] = Authorization::parseAuthorizationString($authorization);

            if (!$this->provider->registerUser($username, $password)) {
                throw SsoException::registerFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'register',
            [
                'username' => $username,
            ],
            $this->provider->getMetadataForCall(
                'register',
                [
                    'username' => $username
                ]
            )
        );
    }

    public function deleteUser(): Response
    {
        try {
            $this->validateCredentials();

            /** @var string|null $username */
            $username = $_POST['username'] ?? null;

            if ($username === null || !$this->provider->deleteUser($username)) {
                throw SsoException::deleteUserFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'deleteUser',
            [
                'username' => $username,
                'metadata' => $this->provider->getMetadataForCall(
                    'deleteUser',
                    [
                        'username' => $username
                    ]
                ),
            ]
        );
    }

    public function login(): Response
    {
        try {
            $this->validateCredentials();

            [$username, $password] = Authorization::parseAuthorizationFromHeader();

            if (!$this->provider->loginUser($username, $password)) {
                throw SsoException::loginFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        $token = $this->provider->generateToken($username);

        return $this->successResponse(
            'login',
            [
                'username' => $username,
                'token' => $token,
            ],
            $this->provider->getMetadataForCall(
                'login',
                [
                    'username' => $username,
                ]
            )
        );
    }

    public function validateToken(): Response
    {
        try {
            $this->validateCredentials();

            [$username, $token] = Authorization::parseAuthorizationFromHeader();

            if (!$this->provider->validateToken($username, $token)) {
                throw SsoException::validateTokenFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'validateToken',
            [
                'username' => $username,
                'token' => $token,
            ],
            $this->provider->getMetadataForCall(
                'validateToken',
                [
                    'username' => $username,
                ]
            )
        );
    }

    public function revokeToken(): Response
    {
        try {
            $this->validateCredentials();

            [$username, $token] = Authorization::parseAuthorizationFromHeader();

            if (!$this->provider->revokeToken($username, $token)) {
                throw SsoException::revokeTokenFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'logout',
            [
                'username' => $username,
            ],
            $this->provider->getMetadataForCall(
                'logout',
                [
                    'username' => $username,
                ]
            )
        );
    }

    public function generateLoginUrl(): Response
    {
        try {
            $this->validateCredentials();

            if (!($this->provider instanceof ExternalProviderInterface)) {
                throw ExternalSsoException::loginUrlGenerationNotSupported();
            }

            $url = $this->provider->generateLoginUrl();

            if (empty($url)) {
                throw ExternalSsoException::loginUrlGenerationFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'generateLoginUrl',
            [
                'url' => $url,
            ],
            $this->provider->getMetadataForCall(
                'generateLoginUrl',
                [
                    'url' => $url
                ]
            )
        );
    }

    public function generateRegisterUrl(): Response
    {
        try {
            $this->validateCredentials();

            if (!($this->provider instanceof ExternalProviderInterface)) {
                throw ExternalSsoException::registerUrlGenerationNotSupported();
            }

            $url = $this->provider->generateRegisterUrl();

            if (empty($url)) {
                throw ExternalSsoException::registerUrlGenerationFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'generateRegisterUrl',
            [
                'url' => $url,
            ],
            $this->provider->getMetadataForCall(
                'generateRegisterUrl',
                [
                    'url' => $url
                ]
            )
        );
    }

    public function registerWithContext(): Response
    {
        try {
            $this->validateCredentials();

            if (!($this->provider instanceof ContextualProviderInterface)) {
                throw ContextualSsoException::registerWithContextNotSupported();
            }

            $response = $this->register();

            if ($response->isError()) {
                throw ContextualSsoException::registerWithContextFailed();
            }

            $username = $response->getFromData('username');
            $context = $_POST['context'] ?? [];

            if (!$this->provider->updateContext($username, $context)) {
                throw ContextualSsoException::registerWithContextFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'registerWithContext',
            [
                'username' => $username,
                'context' => $context,
            ],
            $this->provider->getMetadataForCall(
                'registerWithContext',
                [
                    'username' => $username
                ]
            )
        );
    }

    public function updateContext(): Response
    {
        try {
            $this->validateCredentials();

            if (!($this->provider instanceof ContextualProviderInterface)) {
                throw ContextualSsoException::updateContextNotSupported();
            }

            [$username,] = Authorization::parseAuthorizationFromHeader();

            $context = $_POST['context'] ?? [];

            if (!$this->provider->updateContext($username, $context)) {
                throw ContextualSsoException::updateContextFailed();
            }
        } catch (AbstractException $e) {
            return $this->errorResponseFromException($e);
        }

        return $this->successResponse(
            'updateContext',
            [
                'username' => $username,
                'context' => $context,
            ],
            $this->provider->getMetadataForCall(
                'updateContext',
                [
                    'username' => $username
                ]
            )
        );
    }

    protected function validateCredentials(): void
    {
        if (!$this->provider->validateCredentials($this->clientSecret, $this->clientToken)) {
            throw SsoException::clientCredentialsInvalid();
        }
    }

    protected function successResponse(string $call, array $data = [], array $metadata = []): Response
    {
        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        return new Response(
            ResponseStatus::STATUS_SUCCESS,
            $data,
            $call
        );
    }

    protected function errorResponse(?string $call, string $message, int $code = null): Response
    {
        $data = [
            'message' => $message,
        ];

        if ($code !== null) {
            $data['code'] = $code;
        }

        return new Response(
            ResponseStatus::STATUS_ERROR,
            $data,
            $call,
            $message ?? null,
            $code ?? null
        );
    }

    protected function errorResponseFromException(\Exception $exception): Response
    {
        if ($exception instanceof AbstractException) {
            $call = $exception->getCall();
        }

        return $this->errorResponse(
            $call ?? debug_backtrace()[1]['function'] ?? null,
            $exception->getMessage(),
            $exception->getCode()
        );
    }
}
