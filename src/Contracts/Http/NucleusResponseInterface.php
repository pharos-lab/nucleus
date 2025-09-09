<?php

namespace Nucleus\Contracts\Http;

use Psr\Http\Message\ResponseInterface;

interface NucleusResponseInterface extends ResponseInterface
{
    /**
     * Send response to the client.
     */
    public function send(): void;
}
