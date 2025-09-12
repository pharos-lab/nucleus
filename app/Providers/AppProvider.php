<?php

declare(strict_types=1);

namespace App\Providers;

use Nucleus\Core\Bootstrap\Provider;

class AppProvider extends Provider
{
    public function register(): void
    {
        // Route resolver
        $this->container->bind('foo', fn() => 'bar');
    }
}