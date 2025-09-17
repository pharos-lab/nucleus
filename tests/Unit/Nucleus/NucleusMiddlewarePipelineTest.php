<?php

namespace Tests\Unit\Nucleus;

use Nucleus\Http\Response;
use Nucleus\Core\Application;
use Nucleus\Core\Nucleus;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\Middleware\FakeMiddlewareOne;
use Tests\Fakes\Middleware\FakeMiddlewareTwo;
use Tests\Fakes\Middleware\FakeMiddlewareInterrupt;
use Tests\Fakes\FakeRequest;
use Tests\Fakes\Middleware\MiddlewareLog;
use Tests\Traits\ErrorHandlerIsolation;

class NucleusMiddlewarePipelineTest extends TestCase
{

    use ErrorHandlerIsolation;
    
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application(__DIR__ . '/../../Fakes');

        MiddlewareLog::reset();
    }

    public function testMultipleMiddlewaresPipeline(): void
    {
        $this->app->getRouter()->get('/test', fn() => 'ok')
            ->middleware([FakeMiddlewareOne::class, FakeMiddlewareTwo::class]);

        $kernel = new Nucleus($this->app);
        $request = new FakeRequest('/test', 'GET');

        $response = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $response);

        $this->assertEquals('[one][two]ok', (string) $response->getBody());

        
        $this->assertSame(
            [
                'global action before',
                'one action before',
                'two action before',
                'two action after',
                'one action after',
            ],
            MiddlewareLog::get()
        );
    }

    public function testMiddlewareCanInterruptPipeline(): void
    {
        $this->app->getRouter()->get('/blocked', fn() => 'ok')
            ->middleware([FakeMiddlewareInterrupt::class, FakeMiddlewareOne::class, FakeMiddlewareTwo::class]);

        $kernel = new Nucleus($this->app);

        $request = new FakeRequest('/blocked', 'GET');
        $response = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Blocked by middleware', (string) $response->getBody());
        $this->assertSame(403, $response->getStatusCode());

        // Vérifie que les middlewares après l’interruption n’ont pas été appelés
        $this->assertSame(
            ['global action before','interupt action before'],
            MiddlewareLog::get()
        );  
    }

    public function testGlobalAndRouteMiddlewaresTogether(): void
    {
        // Ajoute un middleware spécifique à la route
        $this->app->getRouter()->get('/with-global', fn() => 'ok')
            ->middleware([FakeMiddlewareOne::class]);

        $kernel = new Nucleus($this->app);
        $request = new FakeRequest('/with-global', 'GET');
        $response = $kernel->handle($request);

        $this->assertEquals('[one]ok', (string) $response->getBody());

        $this->assertSame(
            ['global action before', 'one action before','one action after'],
            MiddlewareLog::get()
        );
    }

    public function testMiddlewareOrderIsRespected(): void
    {
        // Inverse l’ordre des middlewares
        $this->app->getRouter()->get('/reverse', fn() => 'ok')
            ->middleware([FakeMiddlewareTwo::class, FakeMiddlewareOne::class]);

        $kernel = new Nucleus($this->app);
        $request = new FakeRequest('/reverse', 'GET');
        $response = $kernel->handle($request);

        $this->assertEquals('[two][one]ok', (string) $response->getBody());

        $this->assertSame(
            ['global action before', 'two action before', 'one action before', 'one action after', 'two action after'],
            MiddlewareLog::get()
        );
    }
}