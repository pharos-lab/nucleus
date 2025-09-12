<?php

declare(strict_types=1);

namespace Nucleus\Core\Bootstrap;

use Nucleus\Routing\Router;
use Nucleus\Routing\RouteResolver;
use Nucleus\View\View;
use Nucleus\Http\Request;

/**
 * Class NucleusProvider
 *
 * Registers the core services of the framework into the container:
 * - RouteResolver
 * - Router
 * - View
 * - Request
 *
 * @package Nucleus\Core\Bootstrap
 */
class NucleusProvider extends Provider
{
    /**
     * Register core services into the container.
     */
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