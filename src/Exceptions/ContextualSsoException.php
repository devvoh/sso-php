<?php declare(strict_types=1);

namespace SsoPhp\Exceptions;

use SsoPhp\Response\ResponseErrors;

class ContextualSsoException extends AbstractException
{
    public static function updateUserContextFailed(): self
    {
        return new self(
            'Update user context failed',
            ResponseErrors::UPDATE_USER_CONTEXT_FAILED
        );
    }

    public static function updateUserContextNotSupported(): self
    {
        return new self(
            'Update user context not supported by provider',
            ResponseErrors::UPDATE_USER_CONTEXT_NOT_SUPPORTED
        );
    }

    public static function registerUserWithContextFailed(): self
    {
        return new self(
            'Register user with context failed',
            ResponseErrors::REGISTER_USER_WITH_CONTEXT_FAILED
        );
    }

    public static function registerUserWithContextNotSupported(): self
    {
        return new self(
            'Register user with context not supported by provider',
            ResponseErrors::REGISTER_USER_WITH_CONTEXT_NOT_SUPPORTED
        );
    }
}
