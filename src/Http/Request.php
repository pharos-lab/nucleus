<?php

namespace Nucleus\Http;

use Nucleus\Contracts\Http\NucleusRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Request
 *
 * Implementation of a PSR-7 compatible HTTP request.
 * Provides access to request method, URI, headers, cookies,
 * query parameters, parsed body, uploaded files, and attributes.
 *
 * This class also includes helper methods (`query`, `input`, `allQuery`, `allInput`)
 * for convenient access to common request data.
 *
 * @package Nucleus\Http
 */
class Request implements NucleusRequestInterface
{
    /** @var string HTTP method (GET, POST, etc.) */
    protected string $method;

    /** @var UriInterface Request URI */
    protected UriInterface $uri;

    /** @var array<string, array<string>> Request headers */
    protected array $headers = [];

    /** @var array<string, mixed> Query parameters ($_GET) */
    protected array $queryParams = [];

    /** @var array<string, mixed> Parsed body ($_POST) */
    protected array $parsedBody = [];

    /** @var string HTTP protocol version */
    protected string $protocol = '1.1';

    /** @var array<string, mixed> Custom attributes (middleware/controller use) */
    protected array $attributes = [];

    /** @var array<string, mixed> Cookies */
    protected array $cookieParams = [];

    /** @var array Uploaded files */
    protected array $uploadedFiles = [];

    /**
     * Create a new Request instance.
     *
     * @param UriInterface $uri     Request URI
     * @param string       $method  HTTP method
     * @param array        $headers Request headers
     * @param array        $query   Query parameters ($_GET)
     * @param array        $post    Parsed body ($_POST)
     */
    public function __construct(
        UriInterface $uri,
        string $method = 'GET',
        array $headers = [],
        array $query = [],
        array $post = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri ?? new Uri($_SERVER['REQUEST_URI'] ?? '/');
        $this->headers = $headers;
        $this->queryParams = $query;
        $this->parsedBody = $post;
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
    public function getBody(): StreamInterface
    {
        // TODO: return a proper body stream
        return new Stream();
    }

    /** {@inheritdoc} */
    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        // $new->body = $body; // to be implemented
        return $new;
    }

    /** {@inheritdoc} */
    public function getRequestTarget(): string { return $this->uri->getPath(); }

    /** {@inheritdoc} */
    public function withRequestTarget($requestTarget): static { return $this; }

    /** {@inheritdoc} */
    public function getMethod(): string { return $this->method; }

    /** {@inheritdoc} */
    public function withMethod($method): static
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    /** {@inheritdoc} */
    public function getUri(): UriInterface { return $this->uri; }

    /** {@inheritdoc} */
    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $new = clone $this;
        $new->uri = $uri;
        return $new;
    }

    /**
     * Capture the current HTTP request from PHP superglobals.
     *
     * @return self
     */
    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = new Uri($_SERVER['REQUEST_URI'] ?? '/');
        $headers = [];
        $query = $_GET;
        $post  = $_POST;

        return new self($uri, $method, $headers, $query, $post);
    }

    // ---------------------------------------------------------------------
    // PSR-7 ServerRequestInterface methods
    // ---------------------------------------------------------------------

    public function getServerParams(): array { return $_SERVER; }
    public function getCookieParams(): array { return $this->cookieParams; }
    public function withCookieParams(array $cookies): static { $new = clone $this; $new->cookieParams = $cookies; return $new; }
    public function getQueryParams(): array { return $this->queryParams; }
    public function withQueryParams(array $query): static { $new = clone $this; $new->queryParams = $query; return $new; }
    public function getUploadedFiles(): array { return $this->uploadedFiles; }
    public function withUploadedFiles(array $uploadedFiles): static { $new = clone $this; $new->uploadedFiles = $uploadedFiles; return $new; }
    public function getParsedBody(): mixed { return $this->parsedBody; }
    public function withParsedBody($data): static { $new = clone $this; $new->parsedBody = $data; return $new; }
    public function getAttributes(): array { return $this->attributes; }
    public function getAttribute($name, $default = null) { return $this->attributes[$name] ?? $default; }
    public function withAttribute($name, $value): static { $new = clone $this; $new->attributes[$name] = $value; return $new; }
    public function withoutAttribute($name): static { $new = clone $this; unset($new->attributes[$name]); return $new; }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Get a query parameter by key.
     *
     * @param string $key
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    public function query(string $key, $default = null)
    {
        return $this->queryParams[$key] ?? $default;
    }

    /**
     * Get an input value (from parsed body) by key.
     *
     * @param string $key
     * @param mixed $default Default value if not found.
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->parsedBody[$key] ?? $default;
    }

    /**
     * Get all query parameters.
     *
     * @return array<string, mixed>
     */
    public function allQuery(): array
    {
        return $this->queryParams;
    }

    /**
     * Get all input values (parsed body).
     *
     * @return array<string, mixed>
     */
    public function allInput(): array
    {
        return $this->parsedBody;
    }
}