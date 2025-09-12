<?php

namespace Nucleus\Contracts;

use Nucleus\Http\Request;
use Nucleus\Routing\Route;

/**
 * Interface RouterInterface
 *
 * Defines the contract for a router implementation.
 *
 * @package Nucleus\Contracts
 */
interface RouterInterface
{
    /**
     * Register a GET route.
     *
     * @param string $uri The route URI pattern.
     * @param mixed $action The action to execute (callable or controller array).
     * @return Route
     */
    public function get(string $uri, $action): Route;

    /**
     * Register a POST route.
     *
     * @param string $uri The route URI pattern.
     * @param mixed $action The action to execute (callable or controller array).
     * @return Route
     */
    public function post(string $uri, $action): Route;

    /**
     * Dispatch the given request and return the matched route.
     *
     * @param Request $request The HTTP request to dispatch.
     * @return Route|null The matched route or null if no route matches.
     */
    public function dispatch(Request $request): ?Route;
}