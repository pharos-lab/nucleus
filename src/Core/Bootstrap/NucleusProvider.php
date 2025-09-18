<?php

declare(strict_types=1);

namespace Nucleus\Core\Bootstrap;

use Nucleus\Contracts\NucleusLoggerInterface;
use Nucleus\Exceptions\ErrorHandler;
use Nucleus\Routing\Router;
use Nucleus\Routing\RouteResolver;
use Nucleus\View\View;
use Nucleus\Http\Request;
use Nucleus\Logging\DailyFileLogger;
use Nucleus\Logging\FileLogger;
use Nucleus\Logging\NullLogger;

/**
 * Class NucleusProvider
 *
 * Registers the core services of the framework into the container:
 * - RouteResolver
 * - Router
 * - View
 * - Request
 *
 * @package Nucleus\Core\Bootstrap
 */
class NucleusProvider extends Provider
{
    /**
     * Register core services into the container.
     */
    public function register(): void
    {
        // Route resolver
        $this->container->bind(RouteResolver::class, fn($container) => new RouteResolver($container));

        // Router
        $this->container->bind(Router::class, fn($container) => new Router($container->make(RouteResolver::class)));

        // View
        $this->container->bind(View::class, fn() => new View($this->basePath));

        // Request
        $this->container->bind(Request::class, fn() => Request::capture());

        // Logger
        $this->container->bind(NucleusLoggerInterface::class, function () {
            $driver = $this->app->getConfig()->get('logging.driver', 'daily');
            $driverConfig = $this->app->getConfig()->get('logging.drivers')[$driver] ?? [];

			return match ($driver) {
                'single' => new FileLogger(
                    $this->app->getBasePath() . '/' . dirname($driverConfig['path']),
                    $driverConfig['level'] ?? 'debug'
                ),
                'daily' => new DailyFileLogger(
                    $this->app->getBasePath() . '/' . $driverConfig['path'],
                    $driverConfig['level'] ?? 'debug',
                    $driverConfig['days'] ?? 7
                ),
                'null' => new NullLogger(),
                default => new DailyFileLogger(
                    $this->app->getBasePath() . '/storage/logs',
                    $driverConfig['level'] ?? 'debug',
                    $driverConfig['days'] ?? 7
                ),
            };
		});

        //  errors Handler
        $this->container->bind(ErrorHandler::class, fn($container) => new ErrorHandler($container));
    }
}