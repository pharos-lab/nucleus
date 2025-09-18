<?php

namespace Tests\Unit\Application;

use Nucleus\Core\Application;
use Nucleus\Config\Environment;
use Nucleus\Http\Response;
use Nucleus\Logging\FileLogger;
use Tests\Unit\TestCase;

final class ApplicationLoggerIntegrationTest extends TestCase
{
    protected string $logFile;
    protected Application $app;

    protected function setUp(): void
    {
        $basePath = __DIR__ . '/../../Fakes';
        
        if (!is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }

        $this->logFile = $basePath . '/storage/logs/app.log';
        
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        // Crée une application avec basePath temporaire
        $this->app = new Application($basePath);

        // Forcer le driver "file" et chemin
        $this->app->getContainer()->bind(
            \Nucleus\Contracts\NucleusLoggerInterface::class,
            fn() => new FileLogger($this->logFile)
        );
    }

    public function testExceptionIsLoggedAndResponseReturned(): void
    {
        Environment::set('APP_ENV', 'local');

        $exception = new \Exception("Integration Test Boom");

        $handler = $this->app->getContainer()->make(\Nucleus\Exceptions\ErrorHandler::class);
        $response = $handler->handleException($exception);

        // Response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Integration Test Boom', (string)$response->getBody());

        // Fichier log
        $this->assertFileExists($this->logFile);
        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('Integration Test Boom', $content);
        $this->assertStringContainsString('ERROR', $content);
    }
}