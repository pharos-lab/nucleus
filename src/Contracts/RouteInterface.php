<?php

namespace Nucleus\Contracts;

/**
 * Interface RouteInterface
 *
 * Defines the contract for a route object.
 *
 * @package Nucleus\Contracts
 */
interface RouteInterface
{
    /**
     * Assign one or more middleware classes to this route.
     *
     * @param array $middlewares List of middleware class names.
     * @return self
     */
    public function middleware(array $middlewares): self;

    /**
     * Assign a name to the route for URL generation.
     *
     * @param string $name The route name.
     * @return self
     */
    public function name(string $name): self;

    /**
     * Define parameter constraints for the route.
     *
     * @param array $constraints Associative array of parameter names to regex patterns.
     * @return self
     */
    public function where(array $constraints): self;
}