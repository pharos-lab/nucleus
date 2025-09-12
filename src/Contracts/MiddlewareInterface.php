<?php

namespace Nucleus\Contracts;

use Nucleus\Http\Request;
use Nucleus\Http\Response;

/**
 * Interface for middleware.
 * All middleware must implement this to be compatible with the pipeline.
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming request and return a response.
     *
     * @param Request $request The incoming HTTP request.
     * @param callable $next The next middleware or final request handler.
     * @return Response The HTTP response.
     */
    public function handle(Request $request, callable $next): Response;
}