<?php

namespace Nucleus\Contracts\Http;

use Psr\Http\Message\ServerRequestInterface;

interface NucleusRequestInterface extends ServerRequestInterface
{
    /**
     * Get query parameter by key.
     */
    public function query(string $key, $default = null);

    /**
     * Get input (POST/body) parameter by key.
     */
    public function input(string $key, $default = null);

    /**
     * Get all query parameters.
     */
    public function allQuery(): array;

    /**
     * Get all input parameters.
     */
    public function allInput(): array;
}