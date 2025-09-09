<?php

namespace Nucleus\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected string $path;
    protected string $query = '';

    public function __construct(string $uri)
    {
        $parts = parse_url($uri);
        $this->path = $parts['path'] ?? '/';
        $this->query = $parts['query'] ?? '';
    }

    public function getScheme(): string { return ''; }
    public function getAuthority(): string { return ''; }
    public function getUserInfo(): string { return ''; }
    public function getHost(): string { return ''; }
    public function getPort(): ?int { return null; }
    public function getPath(): string { return $this->path; }
    public function getQuery(): string { return $this->query; }
    public function getFragment(): string { return ''; }
    public function withScheme($scheme): static { return $this; }
    public function withUserInfo($user, $password = null): static { return $this; }
    public function withHost($host): static { return $this; }
    public function withPort($port): static { return $this; }
    public function withPath($path): static { $this->path = $path; return $this; }
    public function withQuery($query): static { $this->query = $query; return $this; }
    public function withFragment($fragment): static { return $this; }
    public function __toString(): string { return $this->path . ($this->query ? "?{$this->query}" : ''); }
}
