<?php

namespace Tests\Unit;

use Nucleus\Http\Response;
use Nucleus\Core\Application;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeRequest;
use Tests\Traits\ErrorHandlerIsolation;

class RouteResolverTest extends TestCase
{
    use ErrorHandlerIsolation;
    
    protected Application $app;

    protected function setUp(): void
    {
        $this->app =new Application(__DIR__ . '/../Fakes');
    }
    

    public function testResolveCallableReturnsResponse()
    {
        $action = fn($request) => 'ok';
        $request = new FakeRequest('/test', 'GET');

        $response = $this->app->getRouter()->resolve($action, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('ok', (string) $response->getBody());
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

        $response = $this->app->getRouter()->resolve($action, $request, $params);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('42', (string) $response->getBody());
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

        $response = $this->app->getRouter()->resolve($action, $request, $params);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('7', (string) $response->getBody());
    }

    public function testResolveInvalidActionReturnsNotFound()
    {
        $action = 'non_existent_action';
        $request = new FakeRequest('/invalid', 'GET');

        $response = $this->app->getRouter()->resolve($action, $request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}