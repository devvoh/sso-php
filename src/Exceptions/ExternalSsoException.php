<?php declare(strict_types=1);

namespace SsoPhp\Exceptions;

class ExternalSsoException extends AbstractException
{
    /*
     * Codes for ExternalProviderInterface
     */
    public const LOGIN_URL_GENERATION_FAILED = 300;
    public const LOGIN_URL_GENERATION_NOT_SUPPORTED = 301;
    public const REGISTER_URL_GENERATION_FAILED = 302;
    public const REGISTER_URL_GENERATION_NOT_SUPPORTED = 303;

    public static function loginUrlGenerationFailed(): self
    {
        return new self(
            'Login url generation failed',
            self::LOGIN_URL_GENERATION_FAILED
        );
    }

    public static function loginUrlGenerationNotSupported(): self
    {
        return new self(
            'Login url generation not supported by provider',
            self::LOGIN_URL_GENERATION_NOT_SUPPORTED
        );
    }

    public static function registerUrlGenerationFailed(): self
    {
        return new self(
            'Register url generation failed',
            self::REGISTER_URL_GENERATION_FAILED
        );
    }

    public static function registerUrlGenerationNotSupported(): self
    {
        return new self(
            'Register url generation not supported by provider',
            self::REGISTER_URL_GENERATION_NOT_SUPPORTED
        );
    }
}
