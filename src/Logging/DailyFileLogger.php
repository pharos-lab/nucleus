<?php

declare(strict_types=1);

namespace Nucleus\Logging;

use Psr\Log\AbstractLogger;

/**
 * DailyFileLogger
 *
 * A PSR-3 compliant logger implementation that writes logs into daily files.
 *
 * Features:
 * - Creates log files in the format `app-YYYY-MM-DD.log` inside a given directory.
 * - Appends all log messages of the same day into the same file.
 * - Supports configurable log file retention (e.g., keep last 7 days).
 * - Automatically removes old log files that exceed the retention period.
 * - Supports message interpolation with context values (`{key}` replaced by context data).
 *
 * Usage:
 * ```php
 * $logger = new DailyFileLogger(__DIR__ . '/storage/logs', 7);
 *
 * $logger->info('User {user} logged in', ['user' => 'Alice']);
 * $logger->error('Something went wrong');
 * ```
 *
 * Example log line:
 * ```
 * [2025-09-17 14:32:45] INFO: User Alice logged in
 * ```
 */
class DailyFileLogger extends AbstractLogger
{
    /**
     * Directory where log files will be stored.
     *
     * @var string
     */
    protected string $directory;

    /**
     * Number of days to keep logs before deletion.
     *
     * @var int
     */
    protected int $days;

    /**
     * Create a new DailyFileLogger instance.
     *
     * @param string $directory Path to log storage directory.
     * @param int    $days      Number of days to retain log files (default: 7).
     */
    public function __construct(string $directory, int $days = 7)
    {
        $this->directory = rtrim($directory, '/');
        $this->days = $days;

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level   The log level (e.g., "info", "error", "debug").
     * @param string|\Stringable $message The log message.
     * @param array  $context Context array for message interpolation.
     *
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $filePath = $this->getLogFilePath();

        $date = date('Y-m-d H:i:s');
        $formatted = "[{$date}] " . strtoupper((string) $level) . ': ' . $this->interpolate((string) $message, $context) . PHP_EOL;

        file_put_contents($filePath, $formatted, FILE_APPEND);

        $this->cleanupOldLogs();
    }

    /**
     * Get the path to today's log file.
     *
     * @return string
     */
    protected function getLogFilePath(): string
    {
        $today = date('Y-m-d');
        return "{$this->directory}/app-{$today}.log";
    }

    /**
     * Replace placeholders in message with context values.
     *
     * Example:
     * ```php
     * $logger->info('User {name} logged in', ['name' => 'Bob']);
     * // Output: "User Bob logged in"
     * ```
     *
     * @param string $message
     * @param array  $context
     * @return string
     */
    protected function interpolate(string $message, array $context = []): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = (string) $val;
        }
        return strtr($message, $replace);
    }

    /**
     * Delete log files older than the configured retention period.
     *
     * @return void
     */
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