<?php
namespace SsoPhp;

class Exception extends \Exception
{
    const CLIENT_CREDENTIALS_INVALID     = 10;
    const LOGIN_FAILED                   = 20;
    const TOKEN_VALIDATION_FAILED        = 30;
    const INVALID_AUTHORIZATION_HEADER   = 40;
    const LOGIN_URL_GENERATION_FAILED    = 50;
    const REGISTER_URL_GENERATION_FAILED = 60;
    const TOKEN_REVOCATION_FAILED        = 70;

    /**
     * @return static
     */
    public static function clientCredentialsInvalid()
    {
        return new static("Client credentials invalid", self::CLIENT_CREDENTIALS_INVALID);
    }

    /**
     * @return static
     */
    public static function loginFailed()
    {
        return new static("Login failed", self::LOGIN_FAILED);
    }

    /**
     * @return static
     */
    public static function tokenRevocationFailed()
    {
        return new static("Token revocation failed", self::TOKEN_REVOCATION_FAILED);
    }

    /**
     * @return static
     */
    public static function tokenValidationFailed()
    {
        return new static("Token validation failed", self::TOKEN_VALIDATION_FAILED);
    }

    /**
     * @return static
     */
    public static function invalidAuthorizationHeader()
    {
        return new static("Invalid authorization header", self::INVALID_AUTHORIZATION_HEADER);
    }

    /**
     * @return static
     */
    public static function loginUrlGenerationFailed()
    {
        return new static("Login url generation failed", self::LOGIN_URL_GENERATION_FAILED);
    }

    /**
     * @return static
     */
    public static function registerUrlGenerationFailed()
    {
        return new static("Register url generation failed", self::REGISTER_URL_GENERATION_FAILED);
    }
}
