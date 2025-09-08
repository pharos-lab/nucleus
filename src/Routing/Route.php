<?php

namespace Nucleus\Routing;

use Nucleus\Contracts\RouteInterface;

class Route implements RouteInterface
{
    public string $method;
    public string $path;
    public $action;
    public array $middlewares = [];
    public ?string $name = null;
    public array $constraints = [];
    public array $params = [];

    public function __construct(string $method, string $path, $action)
    {
        $this->method = $method;
        $this->path = $path;
        $this->action = $action;
    }

    public function middleware(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function where(array $constraints): self
    {
        $this->constraints = $constraints;
        return $this;
    }
}