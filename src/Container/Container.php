<?php

declare(strict_types=1);

namespace Nucleus\Container;

use Nucleus\Contracts\ContainerInterface;

/**
 * Simple dependency injection container.
 * Supports binding interfaces or classes to factory closures and automatic resolution of class dependencies.
 */
class Container implements ContainerInterface
{
    /**
     * @var array<string, callable> Stores the bindings (abstract => factory)
     */
    protected array $bindings = [];

    /**
     * @var array<string, mixed> Stores the resolved instances (singleton behavior)
     */
    protected array $instances = [];

    /**
     * @var array<string, mixed> Stores singleton bindings
     */
    protected array $singletons = [];

    /**
     * Bind an abstract type (interface or class) to a factory closure.
     *
     * @param string $abstract The interface or class name.
     * @param callable $factory Factory that returns an instance of the abstract.
     */
    public function bind(string $abstract, callable $factory)
    {
        $this->bindings[$abstract] = $factory;
    }

    public function singleton(string $abstract, callable $factory)
    {
        $this->bindings[$abstract] = $factory;
        $this->singletons[$abstract] = true;
    }

    /**
     * Resolve an instance of the given class.
     * Automatically resolves constructor dependencies recursively.
     *
     * @param string $class The class name to resolve.
     * @return mixed The created instance.
     * @throws \ReflectionException
     */
    public function make(string $class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        if (isset($this->bindings[$class])) {
            if (isset($this->singletons[$class])) {
                $this->instances[$class] = $this->bindings[$class]($this);
                return $this->instances[$class];
            }
            
            return $this->bindings[$class]($this);
        }

        $ref = new \ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $paramClass = $param->getType();
            
            if ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } elseif (!$paramClass->isBuiltin()) {
                $params[] = $this->make($paramClass->getName());
            } else {
                throw new \Exception("Cannot resolve parameter \${$param->getName()} for class {$class}");
            }
        }

        return $ref->newInstanceArgs($params);
    }

    /**
     * Check if a binding exists for the given abstract type.
     *
     * @param string $abstract The class or interface name.
     * @return bool True if a binding exists, false otherwise.
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]);
    }

    /**
     * Get an instance for the given abstract.
     *
     * @param string $abstract The class or interface name.
     * @return mixed The instance.
     * @throws \Exception If no binding is found.
     */
    public function get(string $abstract)
    {
        if ($this->has($abstract)) {
            return $this->make($abstract);
        }

        throw new \Exception("Service {$abstract} not found in container.");
    }
}