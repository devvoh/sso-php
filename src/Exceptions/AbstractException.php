<?php declare(strict_types=1);

namespace SsoPhp\Exceptions;

abstract class AbstractException extends \Exception
{
    /**
     * @var string|null
     */
    protected $call;

    public function __construct(
        string $message = '',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function setCall(string $call): void
    {
        $this->call = $call;
    }

    public function getCall(): ?string
    {
        return $this->call;
    }
}
