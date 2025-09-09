<?php

namespace Tests\Unit;

use Nucleus\Nucleus;
use Nucleus\Routing\Router;
use Nucleus\Routing\Route;
use Nucleus\Http\Response;
use Nucleus\View\View;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeRequest;
use Tests\Fakes\FakeGlobalMiddleware;
use Tests\Fakes\FakeRouteMiddleware;
use Tests\Fakes\FakeControllerAction;

class NucleusTest extends TestCase
{
    protected Router $router;
    protected array $globalMiddlewares;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->globalMiddlewares = [
            FakeGlobalMiddleware::class,
        ];
        View::setBasePath(__DIR__ . '/../Fakes');
    }

    public function testRouteWithoutMiddlewareReturnsControllerResponse(): void
    {
        $route = $this->router->get('/test', new FakeControllerAction());
        $request = new FakeRequest('/test', 'GET');

        $kernel = new Nucleus($this->router, $this->globalMiddlewares);
        $response = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('original', $response->getBody());
        $this->assertEquals('true', $response->getHeader('X-Global'));
    }

    public function testRouteWithRouteMiddlewareExecutesPipeline(): void
    {
        $route = $this->router->get('/test', new FakeControllerAction())
            ->middleware([FakeRouteMiddleware::class]);

        $request = new FakeRequest('/test', 'GET');

        $kernel = new Nucleus($this->router, $this->globalMiddlewares);
        $response = $kernel->handle($request);

        $this->assertEquals('modified', $response->getBody());
        $this->assertEquals('true', $response->getHeader('X-Global'));
    }

    public function testNonMatchingRouteReturnsNotFound(): void
    {
        $this->router->get('/existing', new FakeControllerAction());
        $request = new FakeRequest('/unknown', 'GET');

        $kernel = new Nucleus($this->router, $this->globalMiddlewares);
        $response = $kernel->handle($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRouteParamsArePassedToController(): void
    {
        $this->router->get('/user/{id}', fn($id) => $id);
        $request = new FakeRequest('/user/42', 'GET');

        $kernel = new Nucleus($this->router, $this->globalMiddlewares);
        $response = $kernel->handle($request);

        $this->assertEquals('42', $response->getBody());
    }
}
