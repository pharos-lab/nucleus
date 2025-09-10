<?php

namespace Tests\Unit;

use Nucleus\Core\Application;
use Nucleus\Core\Nucleus;
use Tests\Fakes\FakeRequest;
use Nucleus\Http\Response;
use Nucleus\Routing\Router;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeMiddlewareInterrupt;

class NucleusMiddlewareInterruptTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        
        $this->app = new Application(__DIR__ . '/../Fakes');
    }

    public function testMiddlewareCanInterruptPipeline(): void
    {
        $this->app->getRouter()->get('/blocked', fn() => 'ok')
            ->middleware([FakeMiddlewareInterrupt::class]);

        $kernel = new Nucleus($this->app);

        $request = new FakeRequest('/blocked', 'GET');

        $response = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $response);

        $body = (string) $response->getBody();
        $this->assertEquals('Blocked by middleware', $body);
    }
}
