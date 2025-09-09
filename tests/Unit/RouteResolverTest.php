<?php

namespace Tests\Unit;

use Nucleus\Routing\RouteResolver;
use Nucleus\Http\Response;
use Nucleus\Container\Container;
use Nucleus\View\View;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeRequest;

class RouteResolverTest extends TestCase
{
    protected function setUp(): void
    {
        // Container minimal pour instancier des classes si besoin
        $container = new Container();
        RouteResolver::setContainer($container);
        View::setBasePath(__DIR__ . '/../Fakes');
    }

    public function testResolveCallableReturnsResponse()
    {
        $action = fn($request) => 'ok';
        $request = new FakeRequest('/test', 'GET');

        $response = RouteResolver::resolve($action, $request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('ok', (string)$response);
    }

    public function testResolveInvokableObject()
    {
        $action = new class {
            public function __invoke($request, $id = null)
            {
                return $id ?? 'no-id';
            }
        };

        $request = new FakeRequest('/user/42', 'GET');
        $params = ['id' => 42];

        $response = RouteResolver::resolve($action, $request, $params);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('42', (string)$response);
    }

    public function testResolveControllerAction()
    {
        $controller = new class {
            public function show($request, $id)
            {
                return "user-$id";
            }
        };

        $action = [$controller::class, 'show'];
        $request = new FakeRequest('/user/7', 'GET');
        $params = ['id' => 7];

        $response = RouteResolver::resolve($action, $request, $params);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('7', (string)$response);
    }

    public function testResolveInvalidActionReturnsNotFound()
    {
        $action = 'non_existent_action';
        $request = new FakeRequest('/invalid', 'GET');

        $response = RouteResolver::resolve($action, $request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}