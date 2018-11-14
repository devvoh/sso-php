<?php declare(strict_types=1);

namespace SsoPhp;

use Throwable;

class Exception extends \Exception
{
    public const CLIENT_CREDENTIALS_INVALID = 10;
    public const REGISTER_FAILED = 20;
    public const DELETE_USER_FAILED = 30;
    public const LOGIN_FAILED = 40;
    public const TOKEN_VALIDATION_FAILED = 50;
    public const NO_AUTHORIZATION_HEADER = 60;
    public const INVALID_AUTHORIZATION_HEADER = 70;
    public const LOGIN_URL_GENERATION_FAILED = 80;
    public const LOGIN_URL_GENERATION_NOT_SUPPORTED = 90;
    public const REGISTER_URL_GENERATION_FAILED = 100;
    public const REGISTER_URL_GENERATION_NOT_SUPPORTED = 110;
    public const TOKEN_REVOCATION_FAILED = 120;
    public const UPDATE_CONTEXT_FAILED = 130;
    public const UPDATE_CONTEXT_NOT_SUPPORTED = 140;
    public const REGISTER_WITH_CONTEXT_FAILED = 150;
    public const REGISTER_WITH_CONTEXT_NOT_SUPPORTED = 160;

    /**
     * @var string|null
     */
    private $call;

    public function getCall(): ?string
    {
        return $this->call;
    }

    public function __construct(?string $call, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->call = $call;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return self
     */
    public static function clientCredentialsInvalid(): self
    {
        return new self('connect', 'Client credentials invalid', self::CLIENT_CREDENTIALS_INVALID);
    }

    /**
     * @return self
     */
    public static function registerFailed(): self
    {
        return new self('register', 'Register failed', self::REGISTER_FAILED);
    }

    /**
     * @return self
     */
    public static function deleteUserFailed(): self
    {
        return new self('deleteUser', 'Delete user failed', self::DELETE_USER_FAILED);
    }

    /**
     * @return self
     */
    public static function loginFailed(): self
    {
        return new self('login', 'Login failed', self::LOGIN_FAILED);
    }

    /**
     * @return self
     */
    public static function tokenValidationFailed(): self
    {
        return new self('validateToken', 'Token validation failed', self::TOKEN_VALIDATION_FAILED);
    }

    /**
     * @return self
     */
    public static function noAuthorizationHeader(): self
    {
        return new self(null, 'No authorization header', self::NO_AUTHORIZATION_HEADER);
    }

    /**
     * @return self
     */
    public static function invalidAuthorizationHeader(): self
    {
        return new self(null, 'Invalid authorization header', self::INVALID_AUTHORIZATION_HEADER);
    }

    /**
     * @return self
     */
    public static function loginUrlGenerationFailed(): self
    {
        return new self('generateLoginUrl', 'Login url generation failed', self::LOGIN_URL_GENERATION_FAILED);
    }

    /**
     * @return self
     */
    public static function loginUrlGenerationNotSupported(): self
    {
        return new self('generateLoginUrl', 'Login url generation not supported by provider', self::LOGIN_URL_GENERATION_NOT_SUPPORTED);
    }

    /**
     * @return self
     */
    public static function registerUrlGenerationFailed(): self
    {
        return new self('generateRegisterUrl', 'Register url generation failed', self::REGISTER_URL_GENERATION_FAILED);
    }

    /**
     * @return self
     */
    public static function registerUrlGenerationNotSupported(): self
    {
        return new self('generateRegisterUrl', 'Register url generation not supported by provider', self::REGISTER_URL_GENERATION_NOT_SUPPORTED);
    }

    /**
     * @return self
     */
    public static function tokenRevocationFailed(): self
    {
        return new self('logout', 'Token revocation failed', self::TOKEN_REVOCATION_FAILED);
    }

    /**
     * @return self
     */
    public static function updateContextFailed(): self
    {
        return new self('updateContext', 'Update context failed', self::UPDATE_CONTEXT_FAILED);
    }

    /**
     * @return self
     */
    public static function updateContextNotSupported(): self
    {
        return new self('updateContext', 'Update context not supported by provider', self::UPDATE_CONTEXT_NOT_SUPPORTED);
    }

    /**
     * @return self
     */
    public static function registerWithContextFailed(): self
    {
        return new self('registerWithContext', 'Register with context failed', self::REGISTER_WITH_CONTEXT_FAILED);
    }

    /**
     * @return self
     */
    public static function registerWithContextNotSupported(): self
    {
        return new self('registerWithContext', 'Register with context not supported by provider', self::REGISTER_WITH_CONTEXT__NOT_SUPPORTED);
    }
}
