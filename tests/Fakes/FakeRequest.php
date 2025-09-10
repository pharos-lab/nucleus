<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;
use Nucleus\Http\Uri;

class FakeRequest extends Request
{
    public function __construct(string $path, string $method)
    {
        $this->method = strtoupper($method);
        $this->uri    = new Uri($path);
    }
}