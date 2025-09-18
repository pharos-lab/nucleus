<?php

declare(strict_types=1);

namespace Nucleus\Logging;

use Psr\Log\AbstractLogger;

/**
 * DailyFileLogger
 *
 * Wrapper around FileLogger that generates a daily log file.
 */
class DailyFileLogger extends AbstractLogger
{
    protected string $directory;
    protected int $days;
    protected string $level;

    public function __construct(string $directory, string $level = 'debug', int $days = 7)
    {
        $this->directory = rtrim($directory, '/');
        $this->days = $days;
        $this->level = $level;

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    public function log($level, $message, array $context = []): void
    {
        // refresh the underlying FileLogger (in case midnight passed)
        $logger = new FileLogger($this->getLogFilePath(), $this->level ?? 'debug');

        $logger->log($level, $message, $context);

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
}