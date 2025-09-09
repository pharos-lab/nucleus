<?php

namespace Tests\Unit;

use Tests\Fakes\FakeRequest;
use Nucleus\Http\Response;
use Nucleus\Nucleus;
use Nucleus\Routing\Router;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeMiddlewareInterrupt;

class NucleusMiddlewareInterruptTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testMiddlewareCanInterruptPipeline(): void
    {
        // Route avec un middleware qui interrompt
        $this->router->get('/blocked', fn() => 'ok')
            ->middleware([FakeMiddlewareInterrupt::class]);

        $kernel = new Nucleus($this->router);

        $request = new FakeRequest('/blocked', 'GET');

        $response = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $response);

        // Le middleware a bloqué la suite, la réponse ne contient pas 'ok'
        $this->assertEquals('Blocked by middleware', $response->getBody());
    }
}
