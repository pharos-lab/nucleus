<?php

namespace Tests\Unit\Logging;

use PHPUnit\Framework\TestCase;
use Nucleus\Logging\DailyFileLogger;

class DailyFileLoggerTest extends TestCase
{
    protected string $logDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logDir = __DIR__ . '/../../Fakes/storage/logs';

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

        // Nettoyer avant chaque test
        foreach (glob($this->logDir . '/*.log') as $file) {
            unlink($file);
        }
    }

    /** @test */
    public function testItWriteLogsInDailyFile(): void
    {
        $logger = new DailyFileLogger($this->logDir, 7);

        $logger->info('Hello world');

        $today = date('Y-m-d');
        $file = "{$this->logDir}/app-{$today}.log";

        $this->assertFileExists($file);
        $this->assertStringContainsString('Hello world', file_get_contents($file));
    }

    /** @test */
    public function testMultipleLogsInTheSameLogFile()
    {
        $logger = new DailyFileLogger($this->logDir, 7);

        $logger->info('First log');
        $logger->error('Second log');
        $logger->debug('Third log');

        $today = date('Y-m-d');
        $file = "{$this->logDir}/app-{$today}.log";

        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertStringContainsString('First log', $content);
        $this->assertStringContainsString('Second log', $content);
        $this->assertStringContainsString('Third log', $content);
    }

    /** @test */
    public function testItRotateFileByDate()
    {
        $logger = new DailyFileLogger($this->logDir, 7);

        // Simuler deux jours différents en modifiant la méthode getLogFilePath via reflection ou helper
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // On écrit "hier"
        $fileYesterday = "{$this->logDir}/app-{$yesterday}.log";
        file_put_contents($fileYesterday, "Yesterday's log\n");

        // On écrit aujourd'hui
        $logger->info('Today log');

        $today = date('Y-m-d');
        $fileToday = "{$this->logDir}/app-{$today}.log";

        $this->assertFileExists($fileYesterday);
        $this->assertFileExists($fileToday);
    }

    /** @test */
    public function testItRomovesOldLogFileByDate()
    {
        // On ne garde qu’1 jour
        $logger = new DailyFileLogger($this->logDir, 1);

        $old = date('Y-m-d', strtotime('-5 days'));
        $oldFile = "{$this->logDir}/app-{$old}.log";
        file_put_contents($oldFile, "Old log\n");

        $logger->info('New log');

        $this->assertFileDoesNotExist($oldFile, 'Old log file should be deleted');
    }

    /** @test */
    public function testItKeepsFileInRetention()
    {
        // On garde 7 jours
        $logger = new DailyFileLogger($this->logDir, 7);

        $recent = date('Y-m-d', strtotime('-2 days'));
        $recentFile = "{$this->logDir}/app-{$recent}.log";
        file_put_contents($recentFile, "Recent log\n");

        $logger->info('New log');

        $this->assertFileExists($recentFile, 'Recent log file should not be deleted');
    }
}