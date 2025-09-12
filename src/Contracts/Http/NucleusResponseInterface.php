<?php

namespace Nucleus\Contracts\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface NucleusResponseInterface
 *
 * Extends PSR‑7 ResponseInterface with helper methods
 * for sending responses and creating JSON or shortcut responses.
 */
interface NucleusResponseInterface extends ResponseInterface
{
    /**
     * Send the response to the client, including headers and body.
     */
    public function send(): void;

    /**
     * Create a new response with given content, status code, and headers.
     *
     * @param string $content Response body content.
     * @param int $status HTTP status code.
     * @param array $headers Response headers.
     * @return static
     */
    public static function make(string $content, int $status = 200, array $headers = []): static;

    /**
     * Create a JSON response with given data and status code.
     *
     * @param array $data Data to encode as JSON.
     * @param int $status HTTP status code.
     * @return static
     */
    public static function json(array $data, int $status = 200): static;

    /**
     * Create a 404 Not Found response.
     *
     * @return static
     */
    public static function notFound(): static;
}