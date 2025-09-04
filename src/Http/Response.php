<?php

namespace Nucleus\Http;

class Response
{
    protected string $content;
    protected int $status = 200;
    protected array $headers = [];

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    /**
     * Send the response to the browser
     */
    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }

    /**
     * Create response statically
     */
    public static function make(string $content, int $status = 200, array $headers = []): self
    {
        return new self($content, $status, $headers);
    }

    public static function json(array $data, int $status = 200): self
    {
        return new self(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }
}
