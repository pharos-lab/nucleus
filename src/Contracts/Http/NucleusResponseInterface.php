<?php

namespace Nucleus\Contracts\Http;

use Psr\Http\Message\ResponseInterface;

interface NucleusResponseInterface extends ResponseInterface
{
    /**
     * Send response to the client.
     */
    public function send(): void;

    /**
     * Create a new response with given content, status and headers.
     */
    public static function make(string $content, int $status = 200, array $headers = []): static;

    /**
     * Create a JSON response.
     */
    public static function json(array $data, int $status = 200): static;

    /**
     * Shortcut for a 404 response.
     */
    public static function notFound(): static;
}
