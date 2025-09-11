<?php

declare(strict_types=1);

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
        
        foreach ($constraints as $param => $regex) {
            // Remove delimiters if user provided any (like #...# or /.../)
            if (preg_match('/^(.)(.*)\1$/', $regex, $matches)) {
                $regex = $matches[2];
            }
            $this->constraints[$param] = $regex;
        }
        
        return $this;
    }
}