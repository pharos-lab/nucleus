<?php

namespace Nucleus\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    protected int $status = 200;
    protected array $headers = [];
    protected StreamInterface $body;
    protected string $protocol = '1.1';

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->body = new Stream($content);
        $this->status = $status;
        $this->headers = $headers;
    }

    public function getStatusCode(): int { return $this->status; }
    public function withStatus($code, $reasonPhrase = ''): static
    {
        $new = clone $this;
        $new->status = $code;
        return $new;
    }

    public function getReasonPhrase(): string { return ''; }
    public function getProtocolVersion(): string { return $this->protocol; }
    public function withProtocolVersion($version): static { $new = clone $this; $new->protocol = $version; return $new; }
    public function getHeaders(): array { return $this->headers; }
    public function hasHeader($name): bool { return isset($this->headers[$name]); }
    public function getHeader($name): array { return $this->headers[$name] ?? []; }
    public function getHeaderLine($name): string { return implode(', ', $this->getHeader($name)); }
    public function withHeader($name, $value): static { $new = clone $this; $new->headers[$name] = (array)$value; return $new; }
    public function withAddedHeader($name, $value): static { $new = clone $this; $new->headers[$name] = array_merge($new->headers[$name] ?? [], (array)$value); return $new; }
    public function withoutHeader($name): static { $new = clone $this; unset($new->headers[$name]); return $new; }
    public function getBody(): StreamInterface { return $this->body; }
    public function withBody(StreamInterface $body): static { $new = clone $this; $new->body = $body; return $new; }

    // Helpers
    public static function make(string $content, int $status = 200, array $headers = []): static
    {
        return new static($content, $status, $headers);
    }
    public static function json(array $data, int $status = 200): static
    {
        return new static(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }
    public static function notFound(): static
    {
        return static::make('Not Found', 404);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header("$name: $v", false);
                }
            } else {
                header("$name: $value", true);
            }
        }

        if ($this->body instanceof \Psr\Http\Message\StreamInterface) {
            echo (string) $this->body;
        } else {
            echo $this->body;
        }
    }

}