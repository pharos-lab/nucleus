<?php

declare(strict_types=1);

namespace Nucleus\Container;

use Nucleus\Contracts\ContainerInterface;

class Container implements ContainerInterface
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
            } elseif ($param->isDefaultValueAvailable()) {
                // Si une valeur par défaut existe, on la prend
                $params[] = $param->getDefaultValue();
            } else {
                $params[] = null;
            }
        }

        return $ref->newInstanceArgs($params);
    }
}