<?php

namespace Nucleus\Exceptions;

/**
 * Exception thrown when required parameters for a named route
 * are missing or incorrect.
 *
 * This exception is used when the application tries to generate
 * a URL for a named route but does not provide the expected
 * parameters or provides invalid ones.
 *
 * Example:
 *
 * ```php
 * throw new RouteNamedParametersException("Missing parameter 'id' for route 'user.show'.");
 * ```
 */
class RouteNamedParametersException extends \Exception
{
    /**
     * Default error message.
     *
     * @var string
     */
    protected $message = 'Required parameters for the route are wrong or missing.';

    /**
     * Create a new RouteNamedParametersException instance.
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
