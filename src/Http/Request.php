<?php

namespace Nucleus\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;

class Request implements ServerRequestInterface
{
    protected string $method;
    protected UriInterface $uri;
    protected array $headers = [];
    protected array $queryParams = [];
    protected array $parsedBody = [];
    protected string $protocol = '1.1';
    protected array $attributes = [];
    protected array $cookieParams = [];
    protected array $uploadedFiles = [];

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

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version): static
    {
        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[$name]);
    }

    public function getHeader($name): array
    {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): static
    {
        $new = clone $this;
        $new->headers[$name] = (array)$value;
        return $new;
    }

    public function withAddedHeader($name, $value): static
    {
        $new = clone $this;
        $new->headers[$name] = array_merge($new->headers[$name] ?? [], (array)$value);
        return $new;
    }

    public function withoutHeader($name): static
    {
        $new = clone $this;
        unset($new->headers[$name]);
        return $new;
    }

    public function getBody(): StreamInterface
    {
        // ici tu peux créer un Stream simple qui contient le body
        return new Stream(); // Body doit implémenter StreamInterface
    }

    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        // $new->body = $body;
        return $new;
    }

    public function getRequestTarget(): string
    {
        return $this->uri->getPath();
    }

    public function withRequestTarget($requestTarget): static
    {
        // pour simplifier
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): static
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $new = clone $this;
        $new->uri = $uri;
        return $new;
    }

    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = new Uri($_SERVER['REQUEST_URI'] ?? '/');
        $headers = getallheaders() ?: [];
        $query = $_GET;
        $post  = $_POST;

        return new self($uri, $method, $headers, $query, $post);
    }

    // PSR‑7 ServerRequestInterface additions
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
}
