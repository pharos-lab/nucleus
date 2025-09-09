<?php

namespace Nucleus\Contracts;

use Nucleus\Http\Request;
use Nucleus\Http\Response;

/**
 * Interface for middleware
 * All middleware must implement this to be compatible with the pipeline
 */
interface MiddlewareInterface
{
    /**
     * Handle the request and return a response
     * 
     * @param Request $request Incoming HTTP request
     * @param callable $next Next middleware or final resolver
     * @return Response
     */
    public function handle(Request $request, callable $next): Response;
}