<?php

namespace Tests\Unit;

use Nucleus\Core\Application;
use Nucleus\Core\Nucleus;
use Nucleus\Http\Response;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeRequest;
use Tests\Fakes\FakeRouteMiddleware;
use Tests\Fakes\FakeControllerAction;

class NucleusTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application(__DIR__ . '/../Fakes');
    }

    public function testRouteWithoutMiddlewareReturnsControllerResponse(): void
    {
        $this->app->getRouter()->get('/test', new FakeControllerAction());
        $request = new FakeRequest('/test', 'GET');

        $kernel = new Nucleus($this->app);
        $response = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('original', (string) $response->getBody());
        $this->assertEquals('true', $response->getHeaderLine('X-Global'));
    }

    public function testRouteWithRouteMiddlewareExecutesPipeline(): void
    {
        $route = $this->app->getRouter()->get('/test', new FakeControllerAction())
            ->middleware([FakeRouteMiddleware::class]);

        $request = new FakeRequest('/test', 'GET');

        $kernel = new Nucleus($this->app);
        $response = $kernel->handle($request);

        $this->assertEquals('modified', (string) $response->getBody());
        $this->assertEquals('true', $response->getHeaderLine('X-Global'));
        $this->assertEquals('true', $response->getHeaderLine('X-Route'));
    }

    public function testNonMatchingRouteReturnsNotFound(): void
    {
        $this->app->getRouter()->get('/existing', new FakeControllerAction());
        $request = new FakeRequest('/unknown', 'GET');

        $kernel = new Nucleus($this->app);
        $response = $kernel->handle($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRouteParamsArePassedToController(): void
    {
        $this->app->getRouter()->get('/user/{id}', fn($id) => $id);
        $request = new FakeRequest('/user/42', 'GET');

        $kernel = new Nucleus($this->app);
        $response = $kernel->handle($request);

        $this->assertEquals('42', (string) $response->getBody());
    }
}
