<?php

namespace Tests\Unit;

use Nucleus\Http\Response;
use Nucleus\Core\Application;
use Nucleus\Core\Nucleus;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeMiddlewareOne;
use Tests\Fakes\FakeMiddlewareTwo;
use Tests\Fakes\FakeRequest;

class NucleusMiddlewarePipelineTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application(__DIR__ . '/../Fakes');
    }

    public function testMultipleMiddlewaresPipeline(): void
    {
        $this->app->getRouter()->get('/test', fn() => 'ok')
            ->middleware([FakeMiddlewareOne::class, FakeMiddlewareTwo::class]);

        $kernel = new Nucleus($this->app);
        $request = new FakeRequest('/test', 'GET');

        $response = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $response);

        $body = (string) $response->getBody();

        $this->assertStringContainsString('[one]', $body);
        $this->assertStringContainsString('[two]', $body);

        $this->assertStringContainsString('ok', $body);

        $this->assertEquals('[one][two]ok', $body);
    }
}
