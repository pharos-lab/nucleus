<?php

use PHPUnit\Framework\TestCase;
use Nucleus\Core\Application;
use Nucleus\Routing\Router;
use Nucleus\Routing\RouteResolver;
use Nucleus\View\View;
use Nucleus\Http\Request;
use Tests\Traits\ErrorHandlerIsolation;

class ApplicationCoreProviderTest extends TestCase
{

    use ErrorHandlerIsolation;
    
    public function test_core_services_are_registered()
    {
        $app = new Application(__DIR__ . '/../Fakes');

        $container = $app->getContainer();

        $this->assertTrue($container->has(Router::class));
        $this->assertTrue($container->has(RouteResolver::class));
        $this->assertTrue($container->has(View::class));
        $this->assertTrue($container->has(Request::class));

        $this->assertInstanceOf(Router::class, $container->get(Router::class));
        $this->assertInstanceOf(RouteResolver::class, $container->get(RouteResolver::class));
        $this->assertInstanceOf(View::class, $container->get(View::class));
        $this->assertInstanceOf(Request::class, $container->get(Request::class));
    }
}