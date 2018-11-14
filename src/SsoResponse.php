<?php declare(strict_types=1);

namespace SsoPhp;

class SsoResponse
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
        string $call,
        string $status,
        array $data = [],
        ?string $errorMessage = null,
        ?int $errorCode = null
    ) {
        $this->call = $call;
        $this->status = $status;
        $this->data = $data;
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
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

    public function isSuccess(): bool
    {
        return $this->status === ResponseStatusTypes::STATUS_SUCCESS;
    }

    public function isError(): bool
    {
        return $this->status === ResponseStatusTypes::STATUS_ERROR;
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

    public function toJson(): string
    {
        return json_encode([
            'status' => $this->status,
            'data' => $this->data,
            'call' => $this->call
        ]);
    }

    public static function createFromArray(array $array): self
    {
        if ($array['status'] === ResponseStatusTypes::STATUS_ERROR) {
            $errorMessage = $array['data']['message'];
            $errorCode = $array['data']['code'];
        }

        return new self(
            $array['call'],
            $array['status'],
            $array['data'] ?? [],
            $errorMessage ?? null,
            $errorCode ?? null
        );
    }
}
