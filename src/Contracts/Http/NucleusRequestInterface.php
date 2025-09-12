<?php

namespace Nucleus\Contracts\Http;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface NucleusRequestInterface
 *
 * Extends PSR‑7 ServerRequestInterface with convenient helpers
 * for query and body input access.
 */
interface NucleusRequestInterface extends ServerRequestInterface
{
    /**
     * Retrieve a query (GET) parameter by key.
     *
     * @param string $key Parameter name.
     * @param mixed $default Value to return if parameter is not set.
     * @return mixed
     */
    public function query(string $key, $default = null);

    /**
     * Retrieve an input (POST or parsed body) parameter by key.
     *
     * @param string $key Parameter name.
     * @param mixed $default Value to return if parameter is not set.
     * @return mixed
     */
    public function input(string $key, $default = null);

    /**
     * Get all query (GET) parameters as an associative array.
     *
     * @return array
     */
    public function allQuery(): array;

    /**
     * Get all input (POST/parsed body) parameters as an associative array.
     *
     * @return array
     */
    public function allInput(): array;
}