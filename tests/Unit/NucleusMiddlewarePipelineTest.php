<?php

namespace Tests\Unit;

use Nucleus\Http\Response;
use Nucleus\Nucleus;
use Nucleus\Routing\Router;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\FakeMiddlewareOne;
use Tests\Fakes\FakeMiddlewareTwo;
use Tests\Fakes\FakeRequest;

class NucleusMiddlewarePipelineTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testMultipleMiddlewaresPipeline(): void
    {
        // Route avec plusieurs middlewares
        $route = $this->router->get('/test', fn() => 'ok')
            ->middleware([FakeMiddlewareOne::class, FakeMiddlewareTwo::class]);

        $kernel = new Nucleus($this->router);

        $request = new FakeRequest('/test', 'GET');

        $response = $kernel->handle($request);

        $this->assertInstanceOf(Response::class, $response);

        // Vérifier que chaque middleware a modifié la requête ou ajouté son effet
        $body = $response->getBody();

        // FakeMiddlewareOne ajoute "[one]" et FakeMiddlewareTwo ajoute "[two]"
        $this->assertStringContainsString('[one]', $body);
        $this->assertStringContainsString('[two]', $body);

        // Vérifier que le corps final contient la réponse de l'action
        $this->assertStringContainsString('ok', $body);

        // Vérifier l’ordre : [one][two]ok
        $this->assertEquals('[one][two]ok', $body);
    }
}
