<?php

namespace SsoPhp;

use SsoPhp\Exceptions\SsoException;
use SsoPhp\Provider\ProviderInterface;

class Authorization
{
    public static function buildBasicAuthorization(string $username, string $password): string
    {
        return 'Basic ' . self::buildAuthorization($username, $password);
    }

    public static function buildBearerAuthorization(string $username, string $token): string
    {
        return 'Bearer ' . self::buildAuthorization($username, $token);
    }

    public static function buildAuthorization(string $username, string $passwordOrToken): string
    {
        return base64_encode(sprintf(
            '%s:%s',
            $username,
            $passwordOrToken
        ));
    }

    public static function parsePostAuthorization(): array
    {
        $authorization = $_POST['authorization'] ?? null;

        return self::parseAuthorization($authorization);
    }

    public static function parseAuthorization(?string $authorization = null): array
    {
        $authorizationPassed = $authorization !== null;

        if ($authorization === null) {
            $authorization = self::getAuthorizationFromHeader();
        }

        if (!$authorizationPassed && self::getTypeFromAuthorization($authorization) === null) {
            throw SsoException::invalidAuthorizationHeader();
        }

        $authorization = str_ireplace(['Basic ', 'Bearer '], '', $authorization);

        $authorizationDecoded = base64_decode($authorization);

        $authorizationParts = explode(':', $authorizationDecoded);
        if (count($authorizationParts) !== 2) {
            throw SsoException::invalidAuthorizationHeader();
        }

        return $authorizationParts;
    }

    public static function getTypeFromAuthorization(?string $authorization): ?string
    {
        if (substr($authorization, 0, 5) === 'Basic') {
            return 'basic';
        } elseif (substr($authorization, 0, 6) === 'Bearer') {
            return 'bearer';
        }

        return null;
    }

    /**
     * @throws SsoException
     */
    protected static function getAuthorizationFromHeader(): string
    {
        $headers = getallheaders();

        $authorization = $headers['Authorization'] ?? null;

        if ($authorization === null) {
            throw SsoException::noAuthorizationHeader();
        }

        return $authorization;
    }
}
