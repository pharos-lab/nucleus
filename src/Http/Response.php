<?php

namespace Nucleus\Http;

use Nucleus\Contracts\Http\NucleusResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Response
 *
 * PSR-7 compatible HTTP response implementation.
 * Handles status code, headers, body, and protocol version.
 * Includes helper methods for JSON responses, redirects, and common status shortcuts.
 *
 * @package Nucleus\Http
 */
class Response implements NucleusResponseInterface
{
    /** @var int HTTP status code */
    protected int $status = 200;

    /** @var array<string, mixed> Response headers */
    protected array $headers = [];

    /** @var StreamInterface Response body */
    protected StreamInterface $body;

    /** @var string HTTP protocol version */
    protected string $protocol = '1.1';

    /**
     * Response constructor.
     *
     * @param string $content  Response body content
     * @param int    $status   HTTP status code
     * @param array  $headers  Response headers
     */
    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->body = new Stream($content);
        $this->status = $status;
        $this->headers = $headers;
    }

    /** {@inheritdoc} */
    public function getStatusCode(): int { return $this->status; }

    /** {@inheritdoc} */
    public function withStatus($code, $reasonPhrase = ''): static
    {
        $new = clone $this;
        $new->status = $code;
        return $new;
    }

    /** {@inheritdoc} */
    public function getReasonPhrase(): string
    {
        $phrases = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        ];

        return $phrases[$this->status] ?? '';
    }

    /** {@inheritdoc} */
    public function getProtocolVersion(): string { return $this->protocol; }

    /** {@inheritdoc} */
    public function withProtocolVersion($version): static
    {
        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    /** {@inheritdoc} */
    public function getHeaders(): array { return $this->headers; }

    /** {@inheritdoc} */
    public function hasHeader($name): bool { return isset($this->headers[$name]); }

    /** {@inheritdoc} */
    public function getHeader($name): array { return $this->headers[$name] ?? []; }

    /** {@inheritdoc} */
    public function getHeaderLine($name): string { return implode(', ', $this->getHeader($name)); }

    /** {@inheritdoc} */
    public function withHeader($name, $value): static
    {
        $new = clone $this;
        $new->headers[$name] = (array)$value;
        return $new;
    }

    /** {@inheritdoc} */
    public function withAddedHeader($name, $value): static
    {
        $new = clone $this;
        $new->headers[$name] = array_merge($new->headers[$name] ?? [], (array)$value);
        return $new;
    }

    /** {@inheritdoc} */
    public function withoutHeader($name): static
    {
        $new = clone $this;
        unset($new->headers[$name]);
        return $new;
    }

    /** {@inheritdoc} */
    public function getBody(): StreamInterface { return $this->body; }

    /** {@inheritdoc} */
    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    // ---------------------------------------------------------------------
    // Helper methods
    // ---------------------------------------------------------------------

    /**
     * Create a simple response with body content.
     *
     * @param string $content
     * @param int    $status
     * @param array  $headers
     * @return static
     */
    public static function make(string $content, int $status = 200, array $headers = []): static
    {
        return new static($content, $status, $headers);
    }

    /**
     * Return a JSON response.
     *
     * @param array $data
     * @param int   $status
     * @return static
     */
    public static function json(array $data, int $status = 200): static
    {
        return new static(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }

    /**
     * Return a 404 Not Found response.
     *
     * @return static
     */
    public static function notFound(): static
    {
        return static::make('Not Found', 404);
    }

    /**
     * Send the response to the client.
     *
     * Sets HTTP status code, headers, and outputs body content.
     */
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

        echo (string)$this->body;
    }

    /**
     * Return a redirect response.
     *
     * @param string $url
     * @param int    $status
     * @return static
     */
    public static function redirect(string $url, int $status = 302): static
    {
        return new static('', $status, ['Location' => $url]);
    }
}