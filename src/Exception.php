<?php declare(strict_types=1);

namespace SsoPhp;

class Exception extends \Exception
{
    const CLIENT_CREDENTIALS_INVALID = 10;
    const REGISTER_FAILED = 20;
    const LOGIN_FAILED = 30;
    const TOKEN_VALIDATION_FAILED = 40;
    const NO_AUTHORIZATION_HEADER = 50;
    const INVALID_AUTHORIZATION_HEADER = 60;
    const LOGIN_URL_GENERATION_FAILED = 70;
    const REGISTER_URL_GENERATION_FAILED = 80;
    const TOKEN_REVOCATION_FAILED = 90;

    /**
     * @return self
     */
    public static function clientCredentialsInvalid(): self
    {
        return new self("Client credentials invalid", self::CLIENT_CREDENTIALS_INVALID);
    }

    /**
     * @return self
     */
    public static function registerFailed(): self
    {
        return new self("Register failed", self::REGISTER_FAILED);
    }

    /**
     * @return self
     */
    public static function loginFailed(): self
    {
        return new self("Login failed", self::LOGIN_FAILED);
    }

    /**
     * @return self
     */
    public static function tokenRevocationFailed(): self
    {
        return new self("Token revocation failed", self::TOKEN_REVOCATION_FAILED);
    }

    /**
     * @return self
     */
    public static function tokenValidationFailed(): self
    {
        return new self("Token validation failed", self::TOKEN_VALIDATION_FAILED);
    }

    /**
     * @return self
     */
    public static function noAuthorizationHeader(): self
    {
        return new self("No authorization header", self::NO_AUTHORIZATION_HEADER);
    }

    /**
     * @return self
     */
    public static function invalidAuthorizationHeader(): self
    {
        return new self("Invalid authorization header", self::INVALID_AUTHORIZATION_HEADER);
    }

    /**
     * @return self
     */
    public static function loginUrlGenerationFailed(): self
    {
        return new self("Login url generation failed", self::LOGIN_URL_GENERATION_FAILED);
    }

    /**
     * @return self
     */
    public static function registerUrlGenerationFailed(): self
    {
        return new self("Register url generation failed", self::REGISTER_URL_GENERATION_FAILED);
    }
}
