<?php

namespace Tests\Unit;

use Tests\Fakes\FakeRequest;
use Nucleus\Routing\Router;
use Nucleus\Routing\Route;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testGetRouteRegistersCorrectly(): void
    {
        $route = $this->router->get('/test', fn() => 'ok');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/test', $route->path);
        $this->assertEquals('GET', $route->method);
    }

    public function testPostRouteRegistersCorrectly(): void
    {
        $route = $this->router->post('/submit', fn() => 'ok');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/submit', $route->path);
        $this->assertEquals('POST', $route->method);
    }

    public function testDispatchReturnsRouteWithParams(): void
    {
        $routeRegistered = $this->router->get('/user/{id}', fn() => 'ok');

        $request = new FakeRequest('/user/42', 'GET');

        $route = $this->router->dispatch($request);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(['id' => '42'], $route->params);
    }

    public function testDispatchReturnsNullForNonMatchingRoute(): void
    {
        $this->router->get('/user/{id}', fn() => 'ok');

        $request = new FakeRequest('/unknown', 'GET');

        $route = $this->router->dispatch($request);

        $this->assertNull($route);
    }

    public function testConstraintsAreValidated(): void
    {
        $route = $this->router->get('/user/{id}', fn() => 'ok')->where(['id' => '\d+']);

        $requestValid = new FakeRequest('/user/123', 'GET');
        $requestInvalid = new FakeRequest('/user/abc', 'GET');

        $this->assertInstanceOf(Route::class, $this->router->dispatch($requestValid));
        $this->assertNull($this->router->dispatch($requestInvalid));
    }
}
