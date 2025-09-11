<?php

namespace Nucleus\Exceptions;

class RouteNamedParametersException extends \Exception
{
    protected $message = 'Required parameters for the route wrong or missing.';

    public function __construct($message = "", $code = 0)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message, $code);
    }
}