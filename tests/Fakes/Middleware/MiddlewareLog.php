<?php

namespace Tests\Fakes\Middleware;

class MiddlewareLog
{
    /** @var array<string> */
    private static array $log = [];

    public static function reset(): void
    {
        self::$log = [];
    }

    public static function add(string $entry): void
    {
        self::$log[] = $entry;
    }

    public static function get(): array
    {
        return self::$log;
    }
}
