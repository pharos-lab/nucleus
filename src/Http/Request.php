<?php

namespace Nucleus\Http;

class Request
{
    protected string $method;
    protected string $uri;
    protected string $path;
    protected array $query;
    protected array $post;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path = strtok($this->uri, '?') ?: '/';
        $this->query = $_GET;
        $this->post = $_POST;
    }

    public function getMethod(): string
    {
        return strtoupper($this->method);
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function allQuery(): array
    {
        return $this->query;
    }

    public function allInput(): array
    {
        return $this->post;
    }
}