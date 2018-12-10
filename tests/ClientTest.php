<?php

namespace SsoPhp\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use SsoPhp\CurlRequest;
use SsoPhp\Client;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Client|MockObject
     */
    private $mockSsoClient;

    /**
     * @var CurlRequest|MockObject
     */
    private $mockCurlRequest;

    /**
     * @var string
     */
    private $curlResponse = '';

    public function setUp()
    {
        $this->mockSsoClient = $this->createPartialMock(Client::class, ['createCurlRequest']);
        $this->mockSsoClient->__construct(
            'secret',
            'token',
            'https://client.test',
            [
                999 => 'yes',
            ]
        );

        $this->mockCurlRequest = $this->createPartialMock(CurlRequest::class, ['execute']);

        $this->mockSsoClient
            ->method('createCurlRequest')
            ->willReturnCallback(function (string $url) {
                $this->mockCurlRequest->__construct($url);
                return $this->mockCurlRequest;
            });

        $this->mockCurlRequest
            ->method('execute')
            ->willReturn($this->curlResponse);

        parent::setUp();
    }

    public function testConstructorValues()
    {
        self::assertSame('secret', $this->liberateValue('clientSecret', $this->mockSsoClient));
        self::assertSame('token', $this->liberateValue('clientToken', $this->mockSsoClient));
        self::assertSame('https://client.test/', $this->liberateValue('serverUrl', $this->mockSsoClient));

        self::assertSame(
            [
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                999 => 'yes',
            ],
            $this->liberateValue('options', $this->mockSsoClient)
        );
    }

    public function testConnect()
    {
        $this->mockSsoClient->connect();

        $this->assertSame('https://client.test/connect', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            ['SsoPhp-Client-Secret: secret', 'SsoPhp-Client-Token: token'],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertTrue($this->mockCurlRequest->isGet());
    }

    public function testRegister()
    {
        $this->mockSsoClient->registerUser('user', 'pass');

        $this->assertSame('https://client.test/registerUser', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            ['SsoPhp-Client-Secret: secret', 'SsoPhp-Client-Token: token'],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertSame(
            "authorization=dXNlcjpwYXNz",
            $this->mockCurlRequest->getOption(CURLOPT_POSTFIELDS)
        );

        self::assertTrue($this->mockCurlRequest->isPost());
    }

    public function testDeleteUser()
    {
        $this->mockSsoClient->deleteUser('user');

        $this->assertSame('https://client.test/deleteUser', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            ['SsoPhp-Client-Secret: secret', 'SsoPhp-Client-Token: token'],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertSame(
            "username=user",
            $this->mockCurlRequest->getOption(CURLOPT_POSTFIELDS)
        );

        self::assertTrue($this->mockCurlRequest->isPost());
    }

    public function testLogin()
    {
        $this->mockSsoClient->loginUser('user', 'pass');

        $this->assertSame('https://client.test/loginUser', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            [
                'Authorization: Basic dXNlcjpwYXNz',
                'SsoPhp-Client-Secret: secret',
                'SsoPhp-Client-Token: token',
            ],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertTrue($this->mockCurlRequest->isPost());
    }

    public function testValidateToken()
    {
        $this->mockSsoClient->validateToken('user', 'token');

        $this->assertSame('https://client.test/validateToken', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            [
                'Authorization: Bearer dXNlcjp0b2tlbg==',
                'SsoPhp-Client-Secret: secret',
                'SsoPhp-Client-Token: token',
            ],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertTrue($this->mockCurlRequest->isGet());
    }

    public function testRevokeToken()
    {
        $this->mockSsoClient->revokeToken('user', 'token');

        $this->assertSame('https://client.test/revokeToken', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            [
                'Authorization: Bearer dXNlcjp0b2tlbg==',
                'SsoPhp-Client-Secret: secret',
                'SsoPhp-Client-Token: token',
            ],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertTrue($this->mockCurlRequest->isPost());
    }

    public function testRegisterUserWithContext()
    {
        $this->mockSsoClient->registerUserWithContext('user', 'pass', [
            'context' => 'goes here yo',
        ]);

        $this->assertSame('https://client.test/registerUserWithContext', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            [
                'SsoPhp-Client-Secret: secret',
                'SsoPhp-Client-Token: token',
            ],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertTrue($this->mockCurlRequest->isPost());

        self::assertSame(
            "authorization=dXNlcjpwYXNz&context%5Bcontext%5D=goes+here+yo",
            $this->mockCurlRequest->getOption(CURLOPT_POSTFIELDS)
        );
    }

    public function testUpdateContext()
    {
        $this->mockSsoClient->updateUserContext('user', 'token', [
            'stuff' => 'this be new dawg',
        ]);

        $this->assertSame('https://client.test/updateUserContext', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            [
                'Authorization: Bearer dXNlcjp0b2tlbg==',
                'SsoPhp-Client-Secret: secret',
                'SsoPhp-Client-Token: token',
            ],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertTrue($this->mockCurlRequest->isPost());

        self::assertSame(
            "context%5Bstuff%5D=this+be+new+dawg",
            $this->mockCurlRequest->getOption(CURLOPT_POSTFIELDS)
        );
    }

    public function testGenerateRegisterUrl()
    {
        $this->mockSsoClient->generateRegisterUrl();

        $this->assertSame('https://client.test/generateRegisterUrl', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            [
                'SsoPhp-Client-Secret: secret',
                'SsoPhp-Client-Token: token',
            ],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertTrue($this->mockCurlRequest->isGet());
    }

    public function testGenerateLoginUrl()
    {
        $this->mockSsoClient->generateLoginUrl();

        $this->assertSame('https://client.test/generateLoginUrl', $this->mockCurlRequest->getUrl());
        $this->assertSame(
            [
                'SsoPhp-Client-Secret: secret',
                'SsoPhp-Client-Token: token',
            ],
            $this->mockCurlRequest->getOption(CURLOPT_HTTPHEADER)
        );

        self::assertTrue($this->mockCurlRequest->isGet());
    }

    private function liberateValue(string $name, $object)
    {
        $reflection = new ReflectionClass($object);

        $reflectionProperty = $reflection->getProperty($name);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
