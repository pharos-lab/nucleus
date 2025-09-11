<?php

use PHPUnit\Framework\TestCase;
use Nucleus\Core\Application;

class ApplicationProviderTest extends TestCase
{
    public function test_it_registers_user_providers()
    {
        $app = new Application(__DIR__ . '/../Fakes');

        // le provider doit avoir enregistré le service "foo"
        $this->assertTrue($app->getContainer()->has('foo'));

        $service = $app->getContainer()->get('foo');
        $this->assertSame('bar', $service);
    }

    public function test_it_ignores_non_existing_providers()
    {
        $app = new Application(__DIR__ . '/../Fakes');

        // le container doit rester vide (ou au moins ne pas contenir foo)
        $this->assertFalse($app->getContainer()->has('bar'));
    }
}
