<?php

namespace Nucleus\Http;

use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 *
 * Minimal PSR-7 compatible URI implementation.
 * Only parses and exposes path and query components.
 *
 * @package Nucleus\Http
 */
class Uri implements UriInterface
{
    /** @var string Path component of the URI */
    protected string $path;

    /** @var string Query string of the URI */
    protected string $query = '';

    /**
     * Uri constructor.
     *
     * @param string $uri Full URI string.
     */
    public function __construct(string $uri)
    {
        $parts = parse_url($uri);
        $this->path = $parts['path'] ?? '/';
        $this->query = $parts['query'] ?? '';
    }

    /** {@inheritdoc} */
    public function getScheme(): string { return ''; }

    /** {@inheritdoc} */
    public function getAuthority(): string { return ''; }

    /** {@inheritdoc} */
    public function getUserInfo(): string { return ''; }

    /** {@inheritdoc} */
    public function getHost(): string { return ''; }

    /** {@inheritdoc} */
    public function getPort(): ?int { return null; }

    /** {@inheritdoc} */
    public function getPath(): string { return $this->path; }

    /** {@inheritdoc} */
    public function getQuery(): string { return $this->query; }

    /** {@inheritdoc} */
    public function getFragment(): string { return ''; }

    /** {@inheritdoc} */
    public function withScheme($scheme): static { return $this; }

    /** {@inheritdoc} */
    public function withUserInfo($user, $password = null): static { return $this; }

    /** {@inheritdoc} */
    public function withHost($host): static { return $this; }

    /** {@inheritdoc} */
    public function withPort($port): static { return $this; }

    /** {@inheritdoc} */
    public function withPath($path): static
    {
        $this->path = $path;
        return $this;
    }

    /** {@inheritdoc} */
    public function withQuery($query): static
    {
        $this->query = $query;
        return $this;
    }

    /** {@inheritdoc} */
    public function withFragment($fragment): static { return $this; }

    /** {@inheritdoc} */
    public function __toString(): string
    {
        return $this->path . ($this->query ? "?{$this->query}" : '');
    }
}