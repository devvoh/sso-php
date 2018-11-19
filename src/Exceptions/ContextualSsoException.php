<?php declare(strict_types=1);

namespace SsoPhp\Exceptions;

class ContextualSsoException extends AbstractException
{
    public const UPDATE_CONTEXT_FAILED = 500;
    public const UPDATE_CONTEXT_NOT_SUPPORTED = 501;
    public const REGISTER_WITH_CONTEXT_FAILED = 502;
    public const REGISTER_WITH_CONTEXT_NOT_SUPPORTED = 503;

    public static function updateContextFailed(): self
    {
        return new self(
            'Update context failed',
            self::UPDATE_CONTEXT_FAILED
        );
    }

    public static function updateContextNotSupported(): self
    {
        return new self(
            'Update context not supported by provider',
            self::UPDATE_CONTEXT_NOT_SUPPORTED
        );
    }

    public static function registerWithContextFailed(): self
    {
        return new self(
            'Register with context failed',
            self::REGISTER_WITH_CONTEXT_FAILED
        );
    }

    public static function registerWithContextNotSupported(): self
    {
        return new self(
            'Register with context not supported by provider',
            self::REGISTER_WITH_CONTEXT_NOT_SUPPORTED
        );
    }
}
