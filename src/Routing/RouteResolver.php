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
    
    public static function resolve($action, Request $request, array $params = [])
    {
        $result = null;
        
        if (is_callable($action)) {
            $ref = new ReflectionFunction($action);
            $args = self::resolveArgs($ref->getParameters(), $request, $params);
            $result =  $action(...$args);
        }
        elseif (is_array($action) && count($action) === 2) {
            [$controller, $methodName] = $action;
            $controller = self::$container?->make($controller) ?? new $controller();
            $ref = new ReflectionMethod($controller, $methodName);
            $args = self::resolveArgs($ref->getParameters(), $request, $params);
            $result =  $ref->invokeArgs($controller, $args);
        } 
        else {
            return Response::notFound();
        }

        if ($result instanceof Response) {
            return $result;
        }

        return new Response((string) $result);
    }

    protected static function resolveArgs(array $refParams, Request $request, array $params): array
    {
        $args = [];
        foreach ($refParams as $param) {
            $name = $param->getName();
            $type = $param->getType()?->getName();

            // Injection spéciale du Request
            if ($type === Request::class || $name === 'request') {
                $args[] = $request;
            }
            // Paramètre venant de l’URL
            elseif (isset($params[$name])) {
                $args[] = $params[$name];
            }
            // Classe à instancier via le container
            elseif ($type && class_exists($type)) {
                $args[] = self::$container?->make($type);
            }
            // Sinon → valeur par défaut ou null
            else {
                $args[] = $param->isDefaultValueAvailable()
                    ? $param->getDefaultValue()
                    : null;
            }
        }
        return $args;
    }
}