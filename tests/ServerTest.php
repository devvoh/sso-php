<?php

namespace SsoPhp\Tests;

use PHPUnit\Framework\TestCase;
use SsoPhp\Provider\ProviderInterface;
use SsoPhp\Response\ResponseErrors;
use SsoPhp\Server;

class ServerTest extends TestCase
{
    public function setUp()
    {
        $_POST = [];

        parent::setUp();
    }

    public function testConnectSuccessfully()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token');

        $response = $server->connect();

        self::assertTrue($response->isSuccess());
        self::assertSame('connect', $response->getCall());

        $metadata = $response->getFromData('metadata');

        self::assertSame('secret', $metadata['clientSecret']);
        self::assertSame('token', $metadata['clientToken']);
    }

    public function testConnectUnsuccessfully()
    {
        $server = $this->createNewServerWithSpecificCredentials('nope', 'seriously nope');

        $response = $server->connect();

        self::assertTrue($response->isError());
        self::assertSame('connect', $response->getCall());
        self::assertSame('Client credentials invalid', $response->getErrorMessage());
        self::assertSame(ResponseErrors::CLIENT_CREDENTIALS_INVALID, $response->getErrorCode());
    }

    public function testRegisterUserSuccessfully()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('new_user:pass');

        $response = $server->registerUser();

        self::assertTrue($response->isSuccess());
        self::assertSame('registerUser', $response->getCall());
        self::assertCount(2, $provider->users);
        self::assertArrayHasKey('new_user', $provider->users);
        self::assertSame('pass', $provider->users['new_user']['password']);
    }

    public function testRegisterUserFailsIfNoAuthorizationInPost()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $response = $server->registerUser();

        self::assertTrue($response->isError());
        self::assertSame('registerUser', $response->getCall());
        self::assertCount(1, $provider->users);
        self::assertSame('No authorization', $response->getErrorMessage());
        self::assertSame(ResponseErrors::NO_AUTHORIZATION_HEADER, $response->getErrorCode());
    }

    public function testRegisterUserFailsIfAuthorizationInvalid()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('only_a_user');

        $response = $server->registerUser();

        self::assertTrue($response->isError());
        self::assertSame('registerUser', $response->getCall());
        self::assertCount(1, $provider->users);
        self::assertSame('Invalid authorization', $response->getErrorMessage());
        self::assertSame(ResponseErrors::INVALID_AUTHORIZATION_HEADER, $response->getErrorCode());
    }

    public function testRegisterUserFailsForAlreadyExistingUser()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('user:pass');

        $response = $server->registerUser();

        self::assertTrue($response->isError());
        self::assertSame('registerUser', $response->getCall());
        self::assertCount(1, $provider->users);
        self::assertSame('Register user failed', $response->getErrorMessage());
        self::assertSame(ResponseErrors::REGISTER_USER_FAILED, $response->getErrorCode());
    }

    public function testDeleteUserWorks()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

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
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        $response = $server->deleteUser();

        self::assertTrue($response->isError());
        self::assertSame('deleteUser', $response->getCall());
        self::assertCount(1, $provider->users);
        self::assertSame('Delete user failed', $response->getErrorMessage());
        self::assertSame(ResponseErrors::DELETE_USER_FAILED, $response->getErrorCode());
    }

    public function testLoginUserWorks()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('user:pass');

        $server = $this->createNewServerWithSpecificCredentials('secret', 'token');

        $response = $server->loginUser();

        self::assertTrue($response->isSuccess());
        self::assertNotNull($response->getFromData('token'));
    }

    public function testLoginUserReturnsErrorForWrongPassword()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('user:nope');

        $server = $this->createNewServerWithSpecificCredentials('secret', 'token');

        $response = $server->loginUser();

        self::assertTrue($response->isError());
        self::assertSame('Login user failed', $response->getErrorMessage());
        self::assertSame(ResponseErrors::LOGIN_USER_FAILED, $response->getErrorCode());
    }

    public function testLoginUserReturnsErrorForUnknownUsername()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('nope:nope');

        $server = $this->createNewServerWithSpecificCredentials('secret', 'token');

        $response = $server->loginUser();

        self::assertTrue($response->isError());
        self::assertSame('Login user failed', $response->getErrorMessage());
        self::assertSame(ResponseErrors::LOGIN_USER_FAILED, $response->getErrorCode());
    }

    public function testValidateToken()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('user:pass');

        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        $response = $server->loginUser();

        self::assertTrue($response->isSuccess());

        $token = $response->getFromData('token');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('user:' . $token);

        $response = $server->validateToken();

        self::assertTrue($response->isSuccess());
    }

    public function testValidateTokenReturnsErrorOnInvalidToken()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('user:nope');

        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        $response = $server->validateToken();

        self::assertTrue($response->isError());
        self::assertSame('Token validation failed', $response->getErrorMessage());
        self::assertSame(ResponseErrors::VALIDATE_TOKEN_FAILED, $response->getErrorCode());
    }

    public function testRevokeToken()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode('user:pass');

        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        $response = $server->loginUser();

        $token = $response->getFromData('token');

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . base64_encode('user:' . $token);

        $response = $server->validateToken();

        self::assertTrue($response->isSuccess());

        $response = $server->revokeToken();

        self::assertTrue($response->isSuccess());

        $response = $server->validateToken();

        self::assertTrue($response->isError());
    }

    public function testGenerateRegisterUrl()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token');

        $response = $server->generateRegisterUrl();

        self::assertSame('https://server.test/register', $response->getFromData('url'));
    }

    public function testGenerateLoginUrl()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token');

        $response = $server->generateLoginUrl();

        self::assertSame('https://server.test/login', $response->getFromData('url'));
    }

    public function testRegisterWithContext()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['authorization'] = base64_encode('new_user:pass');
        $_POST['context'] = $context = ['context' => 'stuff'];

        $response = $server->registerUserWithContext();

        self::assertTrue($response->isSuccess());
        self::assertSame('registerUserWithContext', $response->getCall());
        self::assertCount(2, $provider->users);
        self::assertArrayHasKey('new_user', $provider->users);
        self::assertSame('pass', $provider->users['new_user']['password']);
        self::assertSame($context, $response->getFromData('context'));
    }

    public function testUpdateContext()
    {
        $server = $this->createNewServerWithSpecificCredentials('secret', 'token', $provider = new TestProvider());

        self::assertCount(1, $provider->users);

        $_POST['username'] = 'user';
        $_POST['context'] = $context = ['context' => 'stuff'];

        $response = $server->updateUserContext();

        self::assertTrue($response->isSuccess());
        self::assertSame($context, $response->getFromData('context'));
    }

    private function createNewServerWithSpecificCredentials(
        string $clientSecret = null,
        string $clientToken = null,
        ProviderInterface $provider = null
    ): Server {
        $server = new class ($provider ?? new TestProvider()) extends Server {
            public function setCredentials(?string $clientSecret, ?string $clientToken): void
            {
                $this->clientSecret = $clientSecret;
                $this->clientToken = $clientToken;
            }
        };

        $server->setCredentials($clientSecret, $clientToken);

        return $server;
    }
}
