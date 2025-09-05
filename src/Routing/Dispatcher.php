<?php

namespace Nucleus\Routing;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use ReflectionFunction;
use ReflectionMethod;

class Dispatcher
{
    public static function dispatch($action, Request $request, array $params = [])
    {
        if (is_callable($action)) {
            var_dump('action!');
            $ref = new ReflectionFunction($action);
            $args = self::resolveArgs($ref->getParameters(), $request, $params);
            return $action(...$args);
        }

        if (is_array($action) && count($action) === 2) {
            [$controller, $methodName] = $action;
            $controller = new $controller();
            $ref = new ReflectionMethod($controller, $methodName);
            $args = self::resolveArgs($ref->getParameters(), $request, $params);
            return $ref->invokeArgs($controller, $args);
        }

        return Response::notFound();
    }

    protected static function resolveArgs(array $refParams, Request $request, array $params): array
    {
        $args = [];
        foreach ($refParams as $param) {
            $name = $param->getName();
            if ($name === 'request') $args[] = $request;
            elseif (isset($params[$name])) $args[] = $params[$name];
            else $args[] = null;
        }
        return $args;
    }
}