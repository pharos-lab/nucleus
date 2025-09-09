<?php

declare(strict_types=1);

namespace Nucleus\Routing;

use Nucleus\Container\Container;
use Nucleus\Contracts\Http\NucleusRequestInterface;
use Nucleus\Contracts\Http\NucleusResponseInterface;
use Nucleus\Http\Response;
use Nucleus\Nucleus;
use ReflectionFunction;
use ReflectionMethod;

class RouteResolver
{
    protected static ?Container $container = null;

    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * Resolve the action and return a Response
     */
    public static function resolve($action, NucleusRequestInterface $request, array $params = []): NucleusResponseInterface
    {
        $result = null;

        if (self::isCallable($action)) {
            $result = self::resolveCallable($action, $request, $params);
        } elseif (self::isControllerAction($action)) {
            $result = self::resolveController($action, $request, $params);
        } else {
            return Response::notFound();
        }

        return $result instanceof NucleusResponseInterface ? $result : new Response((string) $result);
    }

    protected static function isCallable($action): bool
    {
        return is_callable($action);
    }

    protected static function isControllerAction($action): bool
    {
        return is_array($action) && count($action) === 2;
    }

    protected static function resolveCallable($action, NucleusRequestInterface $request, array $params)
    {
        // Handle invokable objects
        if (is_object($action) && method_exists($action, '__invoke')) {
            $ref = new ReflectionMethod($action, '__invoke');
            $args = self::resolveArgs($ref->getParameters(), $request, $params);
            return $ref->invokeArgs($action, $args);
        }

        // Handle normal closures/functions
        $ref = new ReflectionFunction($action);
        $args = self::resolveArgs($ref->getParameters(), $request, $params);
        return $action(...$args);
    }

    protected static function resolveController(array $action, NucleusRequestInterface $request, array $params)
    {
        [$controllerClass, $methodName] = $action;
        $controller = self::$container?->make($controllerClass) ?? new $controllerClass();
        $ref = new ReflectionMethod($controller, $methodName);
        $args = self::resolveArgs($ref->getParameters(), $request, $params);
        return $ref->invokeArgs($controller, $args);
    }

    /**
     * Resolve method parameters using request, URL params, or container
     */
    protected static function resolveArgs(array $refParams, NucleusRequestInterface $request, array $params): array
    {
        $args = [];

        foreach ($refParams as $param) {
            $name = $param->getName();
            $type = $param->getType()?->getName();

            // Special injection of Request
            if ($type === NucleusRequestInterface::class || $name === 'request') {
                $args[] = $request;
            }
            // URL parameter
            elseif (isset($params[$name])) {
                $args[] = $params[$name];
            }
            // Class resolved via container
            elseif ($type && class_exists($type)) {
                $args[] = self::$container?->make($type);
            }
            // Default value or null
            else {
                $args[] = $param->isDefaultValueAvailable()
                    ? $param->getDefaultValue()
                    : null;
            }
        }

        return $args;
    }
}