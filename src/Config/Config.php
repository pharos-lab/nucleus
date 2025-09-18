<?php

declare(strict_types=1);

namespace Nucleus\Config;

/**
 * Class Config
 *
 * Centralized configuration manager.
 *
 * Responsibilities:
 * - Load configuration files from a directory
 * - Provide get/set access to configuration values using dot notation
 * - Check existence of configuration keys
 * - Support nested configuration arrays
 * - Allow runtime overrides of configuration values
 * - Handle default values for missing keys
 */
class Config
{
    /**
     * Array containing all loaded configuration items.
     *
     * @var array<string, mixed>
     */
    protected array $items = [];

    /**
     * Config constructor.
     *
     * Loads all PHP config files from a directory. Each file should return an array.
     * The filename (without extension) becomes the top-level key.
     *
     * @param string $path Path to the configuration directory
     */
    public function __construct(string $path)
    {
        $path = rtrim($path, '/');

        // Validate the config path
        if (!is_dir($path)) {
            throw new \InvalidArgumentException("Config path '{$path}' does not exist or is not a directory.");
        }

        $files = glob($path . '/*.php');

        // Ensure at least one config file is found
        if (empty($files)) {
            throw new \RuntimeException("No configuration files found in '{$path}'. At least one .php file is required.");
        }

        foreach ($files as $file) {
            if (!is_readable($file)) {
                throw new \RuntimeException("Configuration file '{$file}' is not readable.");
            }

            $key = basename($file, '.php');
            $this->items[$key] = require $file;

            if (!is_array($this->items[$key])) {
                throw new \RuntimeException("Configuration file '{$file}' must return an array.");
            }
        }
    }


    /**
     * Get a configuration value using dot notation.
     *
     * @param string $key The configuration key (e.g., 'database.host')
     * @param mixed $default Value to return if key is not found (default: null)
     * @return mixed The configuration value or $default
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
     *
     * If intermediate keys do not exist, they are created as arrays.
     *
     * @param string $key The configuration key (e.g., 'app.debug')
     * @param mixed $value The value to set
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
     *
     * @param string $key The configuration key (dot notation allowed)
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool
    {
        return $this->get($key, null) !== null;
    }
}