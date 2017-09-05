<?php
namespace SsoPhp\Server;

interface ProviderInterface
{
    /**
     * @param string $clientId
     * @param string $clientToken
     *
     * @return bool
     */
    public function validateCredentials($clientId, $clientToken);

    /**
     * @param string $username
     * @param string $token
     *
     * @return bool
     */
    public function validateToken($username, $token);

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function validateLogin($username, $password);

    /**
     * @param string $username
     * @param string $token
     *
     * @return bool
     */
    public function revokeToken($username, $token);

    /**
     * @param string $username
     *
     * @return string
     */
    public function generateToken($username);

    /**
     * @return string
     */
    public function generateRegisterUrl();

    /**
     * @return string
     */
    public function generateLoginUrl();
}
