<?php
namespace SsoPhp\Server;

interface ProviderInterface
{
    /**
     * Set the client secret for use within the Provider
     *
     * @param string $clientSecret
     *
     * @return static
     */
    public function setClientSecret($clientSecret);

    /**
     * Set the client token for use within the Provider
     *
     * @param string $clientToken
     *
     * @return static
     */
    public function setClientToken($clientToken);

    /**
     * Validate the client secret + token, returning true/false. The credentials are set through
     * self::setClientSecret() and self::setClientToken();
     *
     * @return bool
     */
    public function validateCredentials();

    /**
     * Validate the token by username + token, returning true/false
     *
     * @param string $username
     * @param string $token
     *
     * @return bool
     */
    public function validateToken($username, $token);

    /**
     * Validate the username + password, returning true/false
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function validateLogin($username, $password);

    /**
     * Revoke a token by username + token, returning true/false depending on whether it was successful
     *
     * @param string $username
     * @param string $token
     *
     * @return bool
     */
    public function revokeToken($username, $token);

    /**
     * Generate a token to link to a user
     *
     * @param string $username
     *
     * @return string
     */
    public function generateToken($username);

    /**
     * Generate a register url, linking to a registration page on the Server implementation
     *
     * @return string
     */
    public function generateRegisterUrl();

    /**
     * Generate a register url, linking to a login page on the Server implementation
     *
     * @return string
     */
    public function generateLoginUrl();

    /**
     * Get a user object, array, string, whatever, for the given username. How to implement this is completely
     * dependent upon the implementation on both Client's and Server's side.
     *
     * @param string $context one of the methods calling it defined on \SsoPhp\Server
     * @param array  $data    an array of data provided by the calling methods
     *
     * @return array
     */
    public function getMetadataForContext($context, array $data);
}
