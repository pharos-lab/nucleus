<?php

namespace Nucleus\Contracts;

interface ContainerInterface
{
    /**
     * Bind a class or interface to a factory
     */
    public function bind(string $abstract, callable $factory);

    /**
     * Resolve a class instance
     */
    public function make(string $class);
}