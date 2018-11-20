<?php

namespace SsoPhp;

use SsoPhp\Exceptions\SsoException;

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

    public static function parseAuthorizationFromHeader(): array
    {
        $headers = getallheaders();

        $authorization = $headers['Authorization'] ?? null;

        if ($authorization === null) {
            throw SsoException::noAuthorizationHeader();
        }

        return self::parseAuthorizationString($authorization);
    }

    public static function parseAuthorizationString(string $authorization): array
    {
        $authorization = str_ireplace(['Basic ', 'Bearer '], '', $authorization);

        $authorizationDecoded = base64_decode($authorization);

        $authorizationParts = explode(':', $authorizationDecoded);
        if (count($authorizationParts) !== 2) {
            throw SsoException::invalidAuthorizationHeader();
        }

        return $authorizationParts;
    }

    public static function getTypeFromAuthorization(string $authorization): ?string
    {
        if (substr($authorization, 0, 5) === 'Basic') {
            return 'basic';
        } elseif (substr($authorization, 0, 6) === 'Bearer') {
            return 'bearer';
        }

        return null;
    }
}
