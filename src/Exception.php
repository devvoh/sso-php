<?php
namespace SsoPhp;

class Exception extends \Exception
{
    const CLIENT_ID_NOT_SET              = 10;
    const CLIENT_TOKEN_NOT_SET           = 20;
    const NO_PROVIDER_AVAILABLE          = 30;
    const CLIENT_CREDENTIALS_INVALID     = 40;
    const LOGIN_FAILED                   = 50;
    const TOKEN_VALIDATION_FAILED        = 60;
    const INVALID_AUTHORIZATION_HEADER   = 70;
    const LOGIN_URL_GENERATION_FAILED    = 80;
    const REGISTER_URL_GENERATION_FAILED = 90;
    const SERVER_ENDPOINT_NOT_SET        = 100;
    const SERVER_RETURNED_ERROR          = 110;

    /**
     * @return static
     */
    public static function clientIdNotSet()
    {
        return new static("Client ID not set", self::CLIENT_ID_NOT_SET);
    }

    /**
     * @return static
     */
    public static function clientTokenNotSet()
    {
        return new static("Client token not set", self::CLIENT_TOKEN_NOT_SET);
    }

    /**
     * @return static
     */
    public static function noProviderAvailable()
    {
        return new static("No provider available", self::NO_PROVIDER_AVAILABLE);
    }

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

    /**
     * @return static
     */
    public static function serverEndpointNotSet()
    {
        return new static("Server endpoint not set", self::SERVER_ENDPOINT_NOT_SET);
    }

    /**
     * @return static
     */
    public static function fromServerErrorResponse(array $response)
    {
        return new static("Server returned error: {$response["error"]}", self::SERVER_RETURNED_ERROR);
    }
}
