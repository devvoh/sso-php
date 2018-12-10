<?php declare(strict_types=1);

namespace SsoPhp\Exceptions;

use SsoPhp\Response\ResponseErrors;

class SsoException extends AbstractException
{
    public static function invalidStatusForResponse(string $status): self
    {
        return new self(
            sprintf('Invalid status for response: %s', $status),
            ResponseErrors::INVALID_STATUS_FOR_RESPONSE
        );
    }

    public static function clientCredentialsInvalid(): self
    {
        return new self(
            'Client credentials invalid',
            ResponseErrors::CLIENT_CREDENTIALS_INVALID
        );
    }

    public static function noAuthorizationHeader(): self
    {
        return new self(
            'No authorization',
            ResponseErrors::NO_AUTHORIZATION_HEADER
        );
    }

    public static function invalidAuthorizationHeader(): self
    {
        return new self(
            'Invalid authorization',
            ResponseErrors::INVALID_AUTHORIZATION_HEADER
        );
    }

    public static function registerUserFailed(): self
    {
        return new self(
            'Register user failed',
            ResponseErrors::REGISTER_USER_FAILED
        );
    }

    public static function deleteUserFailed(): self
    {
        return new self(
            'Delete user failed',
            ResponseErrors::DELETE_USER_FAILED
        );
    }

    public static function loginUserFailed(): self
    {
        return new self(
            'Login user failed',
            ResponseErrors::LOGIN_USER_FAILED
        );
    }

    public static function validateTokenFailed(): self
    {
        return new self(
            'Token validation failed',
            ResponseErrors::VALIDATE_TOKEN_FAILED
        );
    }

    public static function revokeTokenFailed(): self
    {
        return new self(
            'Token revocation failed',
            ResponseErrors::REVOKE_TOKEN_FAILED
        );
    }

    public static function secureServerUrlRequired(): self
    {
        return new self(
            'Secure server url required',
            ResponseErrors::SECURE_SERVER_URL_REQUIRED
        );
    }
}
