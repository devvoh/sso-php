<?php declare(strict_types=1);

namespace SsoPhp\Exceptions;

use SsoPhp\Response\ResponseErrors;

class ExternalSsoException extends AbstractException
{
    public static function loginUrlGenerationFailed(): self
    {
        return new self(
            'Login url generation failed',
            ResponseErrors::LOGIN_URL_GENERATION_FAILED
        );
    }

    public static function loginUrlGenerationNotSupported(): self
    {
        return new self(
            'Login url generation not supported by provider',
            ResponseErrors::LOGIN_URL_GENERATION_NOT_SUPPORTED
        );
    }

    public static function registerUrlGenerationFailed(): self
    {
        return new self(
            'Register url generation failed',
            ResponseErrors::REGISTER_URL_GENERATION_FAILED
        );
    }

    public static function registerUrlGenerationNotSupported(): self
    {
        return new self(
            'Register url generation not supported by provider',
            ResponseErrors::REGISTER_URL_GENERATION_NOT_SUPPORTED
        );
    }
}
