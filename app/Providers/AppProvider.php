<?php

declare(strict_types=1);

namespace App\Providers;

use Nucleus\Container\Container;

class AppProvider
{
    protected Container $container;
    protected string $basePath;

    public function __construct(Container $container, string $basePath)
    {
        $this->container = $container;
        $this->basePath = $basePath;
    }

    public function register(): void
    {
        // Route resolver
        $this->container->bind('foo', fn() => 'bar');
    }
}