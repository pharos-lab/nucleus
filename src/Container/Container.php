<?php

namespace Nucleus\Container;

class Container
{
    protected array $bindings = [];

    public function bind(string $abstract, callable $factory)
    {
        $this->bindings[$abstract] = $factory;
    }

    public function make(string $class)
    {
        if (isset($this->bindings[$class])) {
            return $this->bindings[$class]($this);
        }

        $ref = new \ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $paramClass = $param->getType()?->getName();
            if ($paramClass) {
                $params[] = $this->make($paramClass);
            } else {
                $params[] = null;
            }
        }

        return $ref->newInstanceArgs($params);
    }
}