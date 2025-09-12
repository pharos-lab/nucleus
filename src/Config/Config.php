<?php

declare(strict_types=1);

namespace Nucleus\Config;

class Config
{
    protected array $items = [];

    /**
     * Load all config files from a directory.
     */
    public function __construct(string $path)
    {
        $files = glob(rtrim($path, '/') . '/*.php');

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    /**
     * Get a configuration value using dot notation.
     * Returns $default if key is not found.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Set a configuration value at runtime using dot notation.
     */
    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $current =& $this->items;

        foreach ($segments as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current =& $current[$segment];
        }

        $current = $value;
    }

    /**
     * Check if a configuration key exists.
     */
    public function has(string $key): bool
    {
        return $this->get($key, null) !== null;
    }
}