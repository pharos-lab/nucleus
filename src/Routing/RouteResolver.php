<?php

namespace Nucleus\Routing;

use Nucleus\Container\Container;
use Nucleus\Http\Request;
use Nucleus\Http\Response;
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
    public static function resolve($action, Request $request, array $params = []): Response
    {
        $result = null;

        if (self::isCallable($action)) {
            $result = self::resolveCallable($action, $request, $params);
        } elseif (self::isControllerAction($action)) {
            $result = self::resolveController($action, $request, $params);
        } else {
            return Response::notFound();
        }

        return $result instanceof Response ? $result : new Response((string) $result);
    }

    protected static function isCallable($action): bool
    {
        return is_callable($action);
    }

    protected static function isControllerAction($action): bool
    {
        return is_array($action) && count($action) === 2;
    }

    protected static function resolveCallable($action, Request $request, array $params)
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

    protected static function resolveController(array $action, Request $request, array $params)
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
    protected static function resolveArgs(array $refParams, Request $request, array $params): array
    {
        $args = [];

        foreach ($refParams as $param) {
            $name = $param->getName();
            $type = $param->getType()?->getName();

            // Special injection of Request
            if ($type === Request::class || $name === 'request') {
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