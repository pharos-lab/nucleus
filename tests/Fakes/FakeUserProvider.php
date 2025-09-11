<?php

namespace Tests\Fakes;

use Nucleus\Core\Bootstrap\Provider;

class FakeUserProvider extends Provider
{
    public function register(): void
    {
        $this->container->bind('foo', fn() => 'bar');
    }
}