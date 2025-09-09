<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;

class FakeRequest extends Request
{
    public function __construct(string $uri, string $method, $query = [], array $post = [])
    {
        $this->method = strtoupper($method);
        $this->uri    = $uri;
        $this->path   = strtok($uri, '?') ?: '/';
        $this->query  = $query;
        $this->post   = $post;
    }
}