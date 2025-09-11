<?php

namespace Nucleus\Core\Bootstrap;

use Nucleus\Container\Container;
use Nucleus\Core\Application;

abstract class Provider
{
    protected Application $app;
    protected Container $container;
    protected string $basePath;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->container = $app->getContainer();
        $this->basePath = $app->getBasePath();
    }

    /**
     * Chaque provider DOIT implémenter register.
     */
    abstract public function register(): void;
}
