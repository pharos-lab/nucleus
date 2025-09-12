<?php

declare(strict_types=1);

namespace Nucleus\Routing;

use Nucleus\Container\Container;
use Nucleus\Contracts\Http\NucleusRequestInterface;
use Nucleus\Contracts\Http\NucleusResponseInterface;
use Nucleus\Http\Response;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Class RouteResolver
 *
 * Responsible for resolving and executing a route action into a response.
 *
 * Supported action types:
 *  - Closures or functions
 *  - Invokable objects
 *  - Controller actions defined as [ControllerClass::class, 'method']
 *
 * This class uses reflection and the service container to automatically
 * resolve method arguments from:
 *  - The current request
 *  - Route parameters
 *  - Dependencies resolvable via the container
 *  - Default values (if defined)
 */
class RouteResolver
{
    /** @var Container The application service container */
    protected Container $container;

    /**
     * RouteResolver constructor.
     *
     * @param Container $container Service container for dependency resolution
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve the given action into a response.
     *
     * @param mixed                   $action  Route action (callable, controller, etc.)
     * @param NucleusRequestInterface $request Current request
     * @param array<string,mixed>     $params  Route parameters
     *
     * @return NucleusResponseInterface
     */
    public function resolve($action, NucleusRequestInterface $request, array $params = []): NucleusResponseInterface
    {
        $result = null;

        if ($this->isCallable($action)) {
            $result = $this->resolveCallable($action, $request, $params);
        } elseif ($this->isControllerAction($action)) {
            $result = $this->resolveController($action, $request, $params);
        } else {
            return Response::notFound();
        }

        return $result instanceof NucleusResponseInterface
            ? $result
            : new Response((string) $result);
    }

    /**
     * Check if the action is a callable (closure, function, invokable object).
     *
     * @param mixed $action
     * @return bool
     */
    protected function isCallable($action): bool
    {
        return is_callable($action);
    }

    /**
     * Check if the action is a controller reference [Class, Method].
     *
     * @param mixed $action
     * @return bool
     */
    protected function isControllerAction($action): bool
    {
        return is_array($action) && count($action) === 2;
    }

    /**
     * Resolve a callable action (closure, function, invokable object).
     *
     * @param callable                $action
     * @param NucleusRequestInterface $request
     * @param array<string,mixed>     $params
     * @return mixed
     */
    protected function resolveCallable($action, NucleusRequestInterface $request, array $params)
    {
        // Handle invokable objects
        if (is_object($action) && method_exists($action, '__invoke')) {
            $ref = new ReflectionMethod($action, '__invoke');
            $args = $this->resolveArgs($ref->getParameters(), $request, $params);
            return $ref->invokeArgs($action, $args);
        }

        // Handle normal closures/functions
        $ref = new ReflectionFunction($action);
        $args = $this->resolveArgs($ref->getParameters(), $request, $params);
        return $action(...$args);
    }

    /**
     * Resolve a controller action [ControllerClass::class, 'method'].
     *
     * @param array{0: string, 1: string} $action
     * @param NucleusRequestInterface     $request
     * @param array<string,mixed>         $params
     * @return mixed
     */
    protected function resolveController(array $action, NucleusRequestInterface $request, array $params)
    {
        [$controllerClass, $methodName] = $action;

        $controller = $this->container->make($controllerClass);
        $ref = new ReflectionMethod($controller, $methodName);
        $args = $this->resolveArgs($ref->getParameters(), $request, $params);

        return $ref->invokeArgs($controller, $args);
    }

    /**
     * Resolve method/function arguments using:
     *  - The current request
     *  - Route parameters
     *  - Container-resolved dependencies
     *  - Default values if available
     *
     * @param array<int,\ReflectionParameter> $refParams
     * @param NucleusRequestInterface         $request
     * @param array<string,mixed>             $params
     * @return array<int,mixed>
     */
    protected function resolveArgs(array $refParams, NucleusRequestInterface $request, array $params): array
    {
        $args = [];

        foreach ($refParams as $param) {
            $name = $param->getName();
            $type = $param->getType()?->getName();

            // Inject request if type-hinted or named "request"
            if ($type === NucleusRequestInterface::class || $name === 'request') {
                $args[] = $request;
            }
            // Inject from route parameters
            elseif (isset($params[$name])) {
                $args[] = $params[$name];
            }
            // Resolve class from container
            elseif ($type && class_exists($type)) {
                $args[] = $this->container->make($type);
            }
            // Fallback to default value or null
            else {
                $args[] = $param->isDefaultValueAvailable()
                    ? $param->getDefaultValue()
                    : null;
            }
        }

        return $args;
    }
}