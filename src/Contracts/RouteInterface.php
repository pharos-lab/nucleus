<?php

namespace Nucleus\Contracts;

interface RouteInterface
{
    public function middleware(array $middlewares): self;

    public function name(string $name): self;

    public function where(array $constraints): self;
}