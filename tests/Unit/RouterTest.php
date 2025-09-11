<?php

namespace Tests\Unit;

use Nucleus\Core\Application;
use Nucleus\Exceptions\RouteNamedNotFindException;
use Nucleus\Exceptions\RouteNamedParametersException;
use Tests\Fakes\FakeRequest;
use Nucleus\Routing\Router;
use Nucleus\Routing\Route;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router =new Application(__DIR__ . '/../Fakes')->getRouter();
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
        $this->router->get('/user/{id}', fn() => 'ok')->where(['id' => '#[0-9]+#']);

        $requestValid = new FakeRequest('/user/123', 'GET');
        $requestInvalid = new FakeRequest('/user/abc', 'GET');

        $this->assertInstanceOf(Route::class, $this->router->dispatch($requestValid));
        $this->assertNull($this->router->dispatch($requestInvalid));
    }

    public function testRouteUrlWithNoParams(): void
    {
        $this->router->get('/about', fn() => 'ok')->name('about');
        $url = $this->router->routeUrl('about');
        $this->assertEquals('/about', $url);
    }

    public function testRouteUrlWithCorrectParams(): void
    {
        $this->router->get('/user/{id}', fn() => 'ok')->name('user.show');
        $this->router->get('/post/{postId}/comment/{commentId}', fn() => 'ok')->name('comment.show');

        $url = $this->router->routeUrl('user.show', ['id' => 42]);
        $this->assertEquals('/user/42', $url);

        $url2 = $this->router->routeUrl('comment.show', ['postId' => 10, 'commentId' => 5]);
        $this->assertEquals('/post/10/comment/5', $url2);
    }

    public function testRouteUrlMissingParamsThrowsException(): void
    {
        $this->router->get('/user/{id}', fn() => 'ok')->name('user.show');

        $this->expectException(RouteNamedParametersException::class);
        $this->expectExceptionMessage("Missing parameters for route 'user.show': id");

        $this->router->routeUrl('user.show', []);
    }

    public function testRouteUrlExtraParamsThrowsException(): void
    {
        $this->router->get('/user/{id}', fn() => 'ok')->name('user.show');

        $this->expectException(RouteNamedParametersException::class);
        $this->expectExceptionMessage("Extra parameters provided for route 'user.show': foo");

        $this->router->routeUrl('user.show', ['id' => 1, 'foo' => 'bar']);
    }
}