<?php

declare(strict_types=1);

namespace Nucleus\Http;

use Nucleus\View\View;

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
        return;
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

    public static function notFound(): self
    {
        return View::make('errors.404')->status(404);
    }

    /**
     * Set HTTP status code
     */
    public function status(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Set response body
     */
    public function setBody(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get response body
     */
    public function getBody(): string
    {
        return $this->content;
    }

    /**
     * Set a header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get a header value
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function __toString(): string
    {
        return $this->content;
    }

}