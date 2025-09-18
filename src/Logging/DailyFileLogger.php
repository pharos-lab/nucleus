<?php

declare(strict_types=1);

namespace Nucleus\Logging;

use Nucleus\Contracts\NucleusLoggerInterface;
use Psr\Log\AbstractLogger;

/**
 * DailyFileLogger
 *
 * Wrapper around FileLogger that generates a daily log file.
 */
class DailyFileLogger extends AbstractLogger implements NucleusLoggerInterface
{
    protected string $directory;
    protected int $days;
    protected string $level;
    protected FileLogger $logger;

    public static int $loggerCount = 0;

    public function __construct(string $directory, string $level = 'debug', int $days = 7)
    {
        $this->directory = rtrim($directory, '/');
        $this->days = $days;
        $this->level = $level;
        $this->logger = new FileLogger($this->getLogFilePath(), $level);

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }

        self::$loggerCount++;
        var_dump('DailyFileLogger instances: ' . self::$loggerCount);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);

        $this->cleanupOldLogs();
    }

    protected function getLogFilePath(): string
    {
        $today = date('Y-m-d');
        return "{$this->directory}/app-{$today}.log";
    }

    protected function cleanupOldLogs(): void
    {
        $files = glob($this->directory . '/app-*.log');
        $threshold = strtotime("-{$this->days} days");

        foreach ($files as $file) {
            if (preg_match('/app-(\d{4}-\d{2}-\d{2})\.log$/', $file, $matches)) {
                $fileDate = strtotime($matches[1]);
                if ($fileDate < $threshold) {
                    @unlink($file);
                }
            }
        }
    }

    public function withContext(array $context): static
    {
        $this->logger = $this->logger->withContext($context);
        return $this;
    }

    public function success(string $message, array $context = []): void
    {
        $this->logger->success($message, $context);
    }
}