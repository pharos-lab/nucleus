<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Nucleus\Logging\FileLogger;
use Nucleus\Logging\NullLogger;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
    protected string $logFile;

    protected function setUp(): void
    {
        $this->logFile = __DIR__ . '/../temp/test.log';

        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testLogWritesToFile(): void
    {
        $logger = new FileLogger($this->logFile);
        $logger->info('Hello {name}', ['name' => 'World']);

        $this->assertFileExists($this->logFile);
        $content = file_get_contents($this->logFile);

        $this->assertStringContainsString('INFO', $content);
        $this->assertStringContainsString('Hello World', $content);
    }

    public function testContextReplacesPlaceholders(): void
    {
        $logger = new FileLogger($this->logFile);
        $logger->error('User {id} not found', ['id' => 42]);

        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('User 42 not found', $content);
    }

    public function testLevelsShortcutMethodsWork(): void
    {
        $logger = new FileLogger($this->logFile);
        $logger->debug('Debug message');
        $logger->warning('Warning message');

        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('DEBUG', $content);
        $this->assertStringContainsString('Warning message', $content);
    }

    public function testNullLoggerDoesNothing(): void
    {
        $logger = new NullLogger();
        $logger->info('This should not be logged');

        $this->assertFileDoesNotExist($this->logFile);
    }

    public function testLogWithGenericMethod(): void
    {
        $logger = new FileLogger($this->logFile);
        $logger->log(LogLevel::CRITICAL, 'Critical issue');

        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('CRITICAL', $content);
    }

    public function testSuccessMethodLogsAsExpected(): void
    {
        $logger = new FileLogger($this->logFile);
        $logger->success('Operation completed');

        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('SUCCESS', $content);
        $this->assertStringContainsString('Operation completed', $content);
    }

    public function testWithContextAddsPersistentContext(): void
    {
        $logger = new FileLogger($this->logFile);
        $logger = $logger->withContext(['request_id' => 'abc123']);
        $logger->info('Something happened {request_id}', ['request_id' => 'should be overridden']);

        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('should be overridden', $content);
        $this->assertStringContainsString('Something happened', $content);
    }
}