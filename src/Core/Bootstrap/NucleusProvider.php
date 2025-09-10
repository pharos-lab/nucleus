<?php

declare(strict_types=1);

namespace Nucleus\Core\Bootstrap;

use Nucleus\Container\Container;
use Nucleus\Routing\Router;
use Nucleus\Routing\RouteResolver;
use Nucleus\View\View;
use Nucleus\Http\Request;

class NucleusProvider
{
    protected Container $container;
    protected string $basePath;

    public function __construct(Container $container, string $basePath)
    {
        $this->container = $container;
        $this->basePath = $basePath;
    }

    public function register(): void
    {
        // Route resolver
        $this->container->bind(RouteResolver::class, fn($c) => new RouteResolver($c));

        // Router
        $this->container->bind(Router::class, fn($c) => new Router($c->make(RouteResolver::class)));

        // View
        $this->container->bind(View::class, fn() => new View($this->basePath));

        // Request
        $this->container->bind(Request::class, fn() => Request::capture());
    }
}