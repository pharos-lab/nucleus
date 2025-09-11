<?php

namespace Nucleus\Exceptions;

class RouteNamedNotFindException extends \Exception
{
    protected $message = 'No route found for the given request.';

    public function __construct($message = "", $code = 0)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message, $code);
    }
}