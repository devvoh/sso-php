<?php declare(strict_types=1);

namespace SsoPhp;

use SsoPhp\Exceptions\SsoException;
use SsoPhp\Response\ResponseStatus;

class Response
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
     * @var string|null
     */
    private $errorMessage;

    /**
     * @var int|null
     */
    private $errorCode;

    public function __construct(
        string $status,
        array $data = [],
        ?string $call = null,
        ?string $errorMessage = null,
        ?int $errorCode = null
    ) {
        if ($status !== ResponseStatus::STATUS_SUCCESS && $status !== ResponseStatus::STATUS_ERROR) {
            throw SsoException::invalidStatusForResponse($status);
        }

        $this->status = $status;
        $this->data = $data;
        $this->call = $call;

        if ($this->isError()) {
            $this->errorMessage = $errorMessage;
            $this->errorCode = $errorCode;
        }
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getCall(): ?string
    {
        return $this->call;
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
        return $this->data['metadata'][$key] ?? null;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function toJson(): string
    {
        $data = [
            'status' => $this->getStatus(),
            'data' => $this->getData(),
            'call' => $this->getCall()
        ];

        return json_encode($data);
    }

    public function isSuccess(): bool
    {
        return $this->status === ResponseStatus::STATUS_SUCCESS;
    }

    public function isError(): bool
    {
        return $this->status === ResponseStatus::STATUS_ERROR;
    }

    public static function createFromArray(array $array): self
    {
        return new self(
            $array['status'],
            $array['data'] ?? [],
            $array['call'] ?? null,
            $array['data']['message'] ?? null,
            $array['data']['code'] ?? null
        );
    }
}
