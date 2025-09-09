<?php

namespace Nucleus\Contracts;

use Nucleus\Http\Request;
use Nucleus\Routing\Route;

interface RouterInterface
{
    public function get(string $uri, $action): Route;

    public function post(string $uri, $action): Route;

    /**
     * Return the matched route or null if not found
     */
    public function dispatch(Request $request): ?Route;
}