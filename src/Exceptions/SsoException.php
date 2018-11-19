<?php declare(strict_types=1);

namespace SsoPhp\Exceptions;

class SsoException extends AbstractException
{
    public const INVALID_STATUS_FOR_RESPONSE = 100;
    public const CLIENT_CREDENTIALS_INVALID = 101;
    public const NO_AUTHORIZATION_HEADER = 102;
    public const INVALID_AUTHORIZATION_HEADER = 103;
    public const REGISTER_FAILED = 104;
    public const DELETE_USER_FAILED = 105;
    public const LOGIN_FAILED = 106;
    public const VALIDATE_TOKEN_FAILED = 107;
    public const REVOKE_TOKEN_FAILED = 109;
    public const SECURE_SERVER_URL_REQUIRED = 110;

    public static function invalidStatusForResponse(string $status): self
    {
        return new self(
            sprintf('Invalid status for response: %s', $status),
            self::INVALID_STATUS_FOR_RESPONSE
        );
    }

    public static function clientCredentialsInvalid(): self
    {
        return new self(
            'Client credentials invalid',
            self::CLIENT_CREDENTIALS_INVALID
        );
    }

    public static function noAuthorizationHeader(): self
    {
        return new self(
            'No authorization',
            self::NO_AUTHORIZATION_HEADER
        );
    }

    public static function invalidAuthorizationHeader(): self
    {
        return new self(
            'Invalid authorization',
            self::INVALID_AUTHORIZATION_HEADER
        );
    }

    public static function registerFailed(): self
    {
        return new self(
            'Register failed',
            self::REGISTER_FAILED
        );
    }

    public static function deleteUserFailed(): self
    {
        return new self(
            'Delete user failed',
            self::DELETE_USER_FAILED
        );
    }

    public static function loginFailed(): self
    {
        return new self(
            'Login failed',
            self::LOGIN_FAILED
        );
    }

    public static function validateTokenFailed(): self
    {
        return new self(
            'Token validation failed',
            self::VALIDATE_TOKEN_FAILED
        );
    }

    public static function revokeTokenFailed(): self
    {
        return new self(
            'Token revocation failed',
            self::REVOKE_TOKEN_FAILED
        );
    }

    public static function secureServerUrlRequired(): self
    {
        return new self(
            'Secure server url required',
            self::SECURE_SERVER_URL_REQUIRED
        );
    }
}
