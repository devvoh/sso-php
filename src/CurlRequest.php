<?php

namespace SsoPhp;

class CurlRequest
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var resource|null
     */
    private $resource;

    public function __construct(
        string $url,
        array $options = []
    ) {
        $this->url = $url;
        $this->options = $options;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setOption(int $name, $value): void
    {
        $this->options[$name] = $value;
    }

    public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(int $name)
    {
        return $this->options[$name] ?? null;
    }

    public function getMethod(): string
    {
        if ($this->getOption(CURLOPT_POST) === 1) {
            return self::METHOD_POST;
        }

        return self::METHOD_GET;
    }

    public function isGet(): bool
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    public function isPost(): bool
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    public function execute()
    {
        $this->resource = curl_init($this->url);

        foreach ($this->options as $name => $value) {
            curl_setopt($this->resource, $name, $value);
        }

        $response = curl_exec($this->resource);

        curl_close($this->resource);

        return $response;
    }

    public function getInfo(): array
    {
        return curl_getinfo($this->resource);
    }
}
