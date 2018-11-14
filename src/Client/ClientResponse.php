<?php

namespace SsoPhp\Client;

class ClientResponse
{
    /**
     * @var string
     */
    private $call;

    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var string|null
     */
    private $errorMessage;

    /**
     * @var int|null
     */
    private $errorCode;

    public function __construct(array $response)
    {
        $this->call = $response['call'];
        $this->status = $response['status'];

        $this->data = $response['data'] ?? [];
        $this->metadata = $response['data']['metadata'] ?? [];

        if ($this->isError()) {
            $this->errorMessage = $this->getFromData('message');
            $this->errorCode = $this->getFromData('code');
        }
    }

    public function getCall(): string
    {
        return $this->call;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }

    /**
     * @param string|int $key
     *
     * @return mixed
     */
    public function getFromData($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string|int $key
     *
     * @return mixed
     */
    public function getFromMetadata($key)
    {
        return $this->metadata[$key] ?? null;
    }
}
