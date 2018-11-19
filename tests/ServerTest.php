<?php

namespace SsoPhp\Tests;

use SsoPhp\Exceptions\SsoException;
use SsoPhp\Server;

class ServerTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $_POST = [];

        $_SERVER['HTTP_SSO_PHP_CLIENT_SECRET'] = 'secret';
        $_SERVER['HTTP_SSO_PHP_CLIENT_TOKEN'] = 'token';

        parent::setUp();
    }

    public function testConnectSuccessfully()
    {
        $server = new Server($provider = new TestProvider());

        $response = $server->connect();

        self::assertTrue($response->isSuccess());
        self::assertSame('connect', $response->getCall());

        $metadata = $response->getFromData('metadata');

        self::assertSame('secret', $metadata['clientSecret']);
        self::assertSame('token', $metadata['clientToken']);
    }

    public function testConnectUnsuccessfully()
    {
        $_SERVER['HTTP_SSO_PHP_CLIENT_SECRET'] = 'nope';
        $_SERVER['HTTP_SSO_PHP_CLIENT_TOKEN'] = 'seriously nope';

        $server = new Server($provider = new TestProvider());

        $response = $server->connect();

        self::assertTrue($response->isError());
        self::assertSame('connect', $response->getCall());
        self::assertSame('Client credentials invalid', $response->getErrorMessage());
        self::assertSame(SsoException::CLIENT_CREDENTIALS_INVALID, $response->getErrorCode());
    }

    public function testRegisterSuccessfully()
    {
        $server = new Server($provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('new_user:pass');

        $response = $server->register();

        self::assertTrue($response->isSuccess());
        self::assertSame('register', $response->getCall());
        self::assertCount(2, $provider->users);
        self::assertArrayHasKey('new_user', $provider->users);
        self::assertSame('pass', $provider->users['new_user']['password']);
    }

    public function testRegisterFailsIfNoAuthorizationInPost()
    {
        $server = new Server($provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $response = $server->register();

        self::assertTrue($response->isError());
        self::assertSame('register', $response->getCall());
        self::assertCount(1, $provider->users);
        self::assertSame('No authorization', $response->getErrorMessage());
        self::assertSame(SsoException::NO_AUTHORIZATION_HEADER, $response->getErrorCode());
    }

    public function testRegisterFailsIfAuthorizationInvalid()
    {
        $server = new Server($provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('only_a_user');

        $response = $server->register();

        self::assertTrue($response->isError());
        self::assertSame('register', $response->getCall());
        self::assertCount(1, $provider->users);
        self::assertSame('Invalid authorization', $response->getErrorMessage());
        self::assertSame(SsoException::INVALID_AUTHORIZATION_HEADER, $response->getErrorCode());
    }

    public function testRegisterFailsForAlreadyExistingUser()
    {
        $server = new Server($provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('user:pass');

        $response = $server->register();

        self::assertTrue($response->isError());
        self::assertSame('register', $response->getCall());
        self::assertCount(1, $provider->users);
        self::assertSame('Register failed', $response->getErrorMessage());
        self::assertSame(SsoException::REGISTER_FAILED, $response->getErrorCode());
    }

    public function testDeleteUserWorks()
    {
        $server = new Server($provider = new TestProvider());

        $provider->users['deletable'] = ['yup'];

        self::assertArrayHasKey('deletable', $provider->users);
        self::assertCount(2, $provider->users);

        $_POST['username'] = 'deletable';

        $response = $server->deleteUser();

        self::assertTrue($response->isSuccess());
        self::assertSame('deleteUser', $response->getCall());

        self::assertArrayNotHasKey('deletable', $provider->users);
        self::assertCount(1, $provider->users);
    }

    public function testDeleteUserDoesntWorkWithoutUsernameInPost()
    {
        $server = new Server($provider = new TestProvider());

        $response = $server->deleteUser();

        self::assertTrue($response->isError());
        self::assertSame('deleteUser', $response->getCall());
        self::assertCount(1, $provider->users);
        self::assertSame('Delete user failed', $response->getErrorMessage());
        self::assertSame(SsoException::DELETE_USER_FAILED, $response->getErrorCode());
    }

    public function testLoginUserWorks()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('user:pass');

        $server = new Server($provider = new TestProvider());

        $response = $server->login();

        self::assertTrue($response->isSuccess());
        self::assertNotNull($response->getFromData('token'));
    }

    public function testLoginUserReturnsErrorForWrongPassword()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('user:nope');

        $server = new Server($provider = new TestProvider());

        $response = $server->login();

        self::assertTrue($response->isError());
        self::assertSame('Login failed', $response->getErrorMessage());
        self::assertSame(SsoException::LOGIN_FAILED, $response->getErrorCode());
    }

    public function testLoginUserReturnsErrorForUnknownUsername()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('nope:nope');

        $server = new Server($provider = new TestProvider());

        $response = $server->login();

        self::assertTrue($response->isError());
        self::assertSame('Login failed', $response->getErrorMessage());
        self::assertSame(SsoException::LOGIN_FAILED, $response->getErrorCode());
    }

    public function testValidateToken()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('user:pass');

        $server = new Server($provider = new TestProvider());

        $response = $server->login();

        $token = $response->getFromData('token');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('user:' . $token);

        $server = new Server($provider);

        $response = $server->validateToken();

        self::assertTrue($response->isSuccess());
    }

    public function testValidateTokenReturnsErrorOnInvalidToken()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('user:nope');

        $server = new Server($provider = new TestProvider());

        $response = $server->validateToken();

        self::assertTrue($response->isError());
        self::assertSame('Token validation failed', $response->getErrorMessage());
        self::assertSame(SsoException::VALIDATE_TOKEN_FAILED, $response->getErrorCode());
    }

    public function testRevokeToken()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('user:pass');

        $server = new Server($provider = new TestProvider());

        $response = $server->login();

        $token = $response->getFromData('token');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('user:' . $token);

        $server = new Server($provider);

        $response = $server->validateToken();

        self::assertTrue($response->isSuccess());

        $response = $server->revokeToken();

        self::assertTrue($response->isSuccess());

        $response = $server->validateToken();

        self::assertTrue($response->isError());
    }

    public function testGenerateRegisterUrl()
    {
        $server = new Server($provider = new TestProvider());

        $response = $server->generateRegisterUrl();

        self::assertSame('https://server.test/register', $response->getFromData('url'));
    }

    public function testGenerateLoginUrl()
    {
        $server = new Server($provider = new TestProvider());

        $response = $server->generateLoginUrl();

        self::assertSame('https://server.test/login', $response->getFromData('url'));
    }

    public function testRegisterWithContext()
    {
        $server = new Server($provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('new_user:pass');
        $_POST['context'] = $context = ['context' => 'stuff'];

        $response = $server->registerWithContext();

        self::assertTrue($response->isSuccess());
        self::assertSame('registerWithContext', $response->getCall());
        self::assertCount(2, $provider->users);
        self::assertArrayHasKey('new_user', $provider->users);
        self::assertSame('pass', $provider->users['new_user']['password']);
        self::assertSame($context, $response->getFromData('context'));
    }

    public function testUpdateContext()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('user:token');

        $server = new Server($provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('user:token');
        $_POST['context'] = $context = ['context' => 'stuff'];

        $response = $server->updateContext();

        self::assertTrue($response->isSuccess());
        self::assertSame($context, $response->getFromData('context'));
    }

    private function liberateValue(string $name, $object)
    {
        $reflection = new ReflectionClass($object);

        $reflectionProperty = $reflection->getProperty($name);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
