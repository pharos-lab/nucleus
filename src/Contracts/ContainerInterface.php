<?php

namespace Nucleus\Contracts;

/**
 * Interface ContainerInterface
 *
 * Defines a simple dependency injection container.
 */
interface ContainerInterface
{
    /**
     * Bind an abstract type (class or interface) to a factory callable.
     *
     * @param string $abstract Class or interface name.
     * @param callable $factory Factory function that returns an instance.
     */
    public function bind(string $abstract, callable $factory);

    /**
     * Resolve and return an instance of the given class or interface.
     *
     * @param string $class Class or interface name to resolve.
     * @return mixed
     */
    public function make(string $class);
}