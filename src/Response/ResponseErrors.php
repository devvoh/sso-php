<?php declare(strict_types=1);

namespace SsoPhp\Response;

class ResponseErrors
{
    /**
     * General error codes
     */
    public const INVALID_STATUS_FOR_RESPONSE = 1000;
    public const CLIENT_CREDENTIALS_INVALID = 1010;
    public const NO_AUTHORIZATION_HEADER = 1020;
    public const INVALID_AUTHORIZATION_HEADER = 1030;
    public const REGISTER_USER_FAILED = 1040;
    public const DELETE_USER_FAILED = 1050;
    public const LOGIN_USER_FAILED = 1060;
    public const VALIDATE_TOKEN_FAILED = 1070;
    public const REVOKE_TOKEN_FAILED = 1090;
    public const SECURE_SERVER_URL_REQUIRED = 1100;

    /**
     * External error codes
     */
    public const LOGIN_URL_GENERATION_FAILED = 3000;
    public const LOGIN_URL_GENERATION_NOT_SUPPORTED = 3010;
    public const REGISTER_URL_GENERATION_FAILED = 3020;
    public const REGISTER_URL_GENERATION_NOT_SUPPORTED = 3030;

    /**
     * Contextual error codes
     */
    public const UPDATE_USER_CONTEXT_FAILED = 5000;
    public const UPDATE_USER_CONTEXT_NOT_SUPPORTED = 5010;
    public const REGISTER_USER_WITH_CONTEXT_FAILED = 5020;
    public const REGISTER_USER_WITH_CONTEXT_NOT_SUPPORTED = 5030;
}
