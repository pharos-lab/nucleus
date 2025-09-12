<?php

namespace Nucleus\Core\Bootstrap;

use Nucleus\Container\Container;
use Nucleus\Core\Application;

/**
 * Class Provider
 *
 * Base class for service providers.
 * A service provider is responsible for registering bindings
 * and services into the application container.
 *
 * @package Nucleus\Core\Bootstrap
 */
abstract class Provider
{
    /** @var Application Reference to the application instance */
    protected Application $app;

    /** @var Container Reference to the application container */
    protected Container $container;

    /** @var string Base path of the application */
    protected string $basePath;

    /**
     * Provider constructor.
     *
     * @param Application $app The application instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->container = $app->getContainer();
        $this->basePath = $app->getBasePath();
    }

    /**
     * Register services or bindings into the container.
     * 
     * Each provider MUST implement this method.
     */
    abstract public function register(): void;
}