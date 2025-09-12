<?php

declare(strict_types=1);

namespace Nucleus\Exceptions;

/**
 * Exception thrown when a named route cannot be found.
 *
 * This exception is used by the routing system when the application
 * attempts to generate a URL or resolve a route by its name, but no
 * matching entry exists in the route registry.
 *
 * Example:
 *
 * ```php
 * throw new RouteNamedNotFindException("Route 'dashboard' not found.");
 * ```
 */
class RouteNamedNotFindException extends \Exception
{
    /**
     * Default error message.
     *
     * @var string
     */
    protected $message = 'No route found for the given request.';

    /**
     * Create a new RouteNamedNotFindException instance.
     *
     * @param string $message Optional custom error message.
     * @param int    $code    Optional error code.
     */
    public function __construct(string $message = "", int $code = 0)
    {
        if ($message) {
            $this->message = $message;
        }

        parent::__construct($this->message, $code);
    }
}