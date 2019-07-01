<?php

namespace SsoPhp\Tests;

use PHPUnit\Framework\TestCase;
use SsoPhp\Exceptions\SsoException;
use SsoPhp\Response;

class ResponseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testCanBeCreatedWithOnlyStatus()
    {
        $response = new Response('success');

        self::assertInstanceOf(Response::class, $response);
        self::assertSame('success', $response->getStatus());
        self::assertTrue($response->isSuccess());
        self::assertFalse($response->isError());
        self::assertSame([], $response->getData());

        self::assertNull($response->getFromData('anything'));
        self::assertNull($response->getFromMetadata('anything'));

        self::assertNull($response->getErrorMessage());
        self::assertNull($response->getErrorCode());
    }

    public function testErrorStatus()
    {
        $response = new Response('error');

        self::assertTrue($response->isError());
        self::assertFalse($response->isSuccess());
    }

    public function testThrowsExceptionOnInvalidStatus()
    {
        $this->expectException(SsoException::class);
        $this->expectExceptionMessage('Invalid status for response: invalid');

        new Response('invalid');
    }

    public function testDataCanBeSetAndRetrieved()
    {
        $response = new Response(
            'success',
            [
                'test' => 'yes'
            ]
        );

        self::assertArrayHasKey('test', $response->getData());
        self::assertSame('yes', $response->getFromData('test'));
    }

    public function testMetadataCanBeSetAndRetrieved()
    {
        $response = new Response(
            'success',
            [
                'metadata' => [
                    'totes' => 'yes'
                ],
            ]
        );

        self::assertArrayHasKey('metadata', $response->getData());
        self::assertSame(
            [
                'totes' => 'yes',
            ],
            $response->getFromData('metadata')
        );
        self::assertSame('yes', $response->getFromMetadata('totes'));
    }

    public function testCallIsHandledProperly()
    {
        $response = new Response('error', [], 'thisIsACall');

        self::assertSame('thisIsACall', $response->getCall());
    }

    public function testErrorMessageAndCodeSetIfProvidedAndStatusIsError()
    {
        $response = new Response('error', [], null, 'error message', 1337);

        self::assertTrue($response->isError());
        self::assertSame('error message', $response->getErrorMessage());
        self::assertSame(1337, $response->getErrorCode());
    }

    public function testErrorMessageAndCodeNotSetIfProvidedAndStatusIsSuccess()
    {
        $response = new Response('success', [], null, 'error message', 1337);

        self::assertTrue($response->isSuccess());
        self::assertSame(null, $response->getErrorMessage());
        self::assertSame(null, $response->getErrorCode());
    }

    public function testToJsonForSuccess()
    {
        $response = new Response(
            'success',
            [
                'data_value' => 'yes please',
                'metadata' => [
                    'hello' => 'stuff',
                ],
            ],
            'testingJson'
        );

        self::assertSame(
            '{"status":"success","data":{"data_value":"yes please","metadata":{"hello":"stuff"}},"call":"testingJson"}',
            $response->toJson()
        );
    }

    public function testToJsonForError()
    {
        $response = new Response(
            'success',
            [
                'message' => 'nope please',
                'code' => 999,
            ],
            'testingError'
        );

        self::assertSame(
            '{"status":"success","data":{"message":"nope please","code":999},"call":"testingError"}',
            $response->toJson()
        );
    }

    public function testSuccessfulResponseCreateFromArray()
    {
        $response = Response::createFromArray([
            'status' => 'success',
            'data' => [
                'test'=> 'ok',
                'metadata' => [
                    'username' => 'yay!',
                ],
                'message' => 'this is an error message',
                'code' => 178,
            ],
            'call' => 'createFromArray'
        ]);

        self::assertTrue($response->isSuccess());
        self::assertSame('createFromArray', $response->getCall());
        self::assertSame('ok', $response->getFromData('test'));
        self::assertSame('yay!', $response->getFromMetadata('username'));

        self::assertNull($response->getErrorMessage());
        self::assertNull($response->getErrorCode());
    }

    public function testErrorResponseCreateFromArray()
    {
        $response = Response::createFromArray([
            'status' => 'error',
            'data' => [
                'test'=> 'not ok',
                'message' => 'this is an error message',
                'code' => 178,
            ],
            'call' => 'createFromArray'
        ]);

        self::assertTrue($response->isError());
        self::assertSame('createFromArray', $response->getCall());
        self::assertSame('not ok', $response->getFromData('test'));

        self::assertSame('this is an error message', $response->getFromData('message'));
        self::assertSame(178, $response->getFromData('code'));

        self::assertSame('this is an error message', $response->getErrorMessage());
        self::assertSame(178, $response->getErrorCode());
    }
}
