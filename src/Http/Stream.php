<?php

namespace Nucleus\Http;

use Psr\Http\Message\StreamInterface;

/**
 * Class Stream
 *
 * PSR-7 compatible stream implementation using a temporary in-memory resource.
 * Can be used as the body for Request or Response objects.
 *
 * @package Nucleus\Http
 */
class Stream implements StreamInterface
{
    /** @var resource|null Underlying PHP stream resource */
    protected $resource;

    /**
     * Stream constructor.
     *
     * @param string $content Initial content for the stream.
     */
    public function __construct($content = '')
    {
        $this->resource = fopen('php://temp', 'r+');
        if ($content !== '') {
            fwrite($this->resource, $content);
            rewind($this->resource);
        }
    }

    /**
     * Convert the stream to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $this->rewind();
        return stream_get_contents($this->resource);
    }

    /** {@inheritdoc} */
    public function close(): void
    {
        fclose($this->resource);
    }

    /** {@inheritdoc} */
    public function detach()
    {
        $res = $this->resource;
        $this->resource = null;
        return $res;
    }

    /** {@inheritdoc} */
    public function getSize(): ?int
    {
        $stats = fstat($this->resource);
        return $stats['size'] ?? null;
    }

    /** {@inheritdoc} */
    public function tell(): int
    {
        return ftell($this->resource);
    }

    /** {@inheritdoc} */
    public function eof(): bool
    {
        return feof($this->resource);
    }

    /** {@inheritdoc} */
    public function isSeekable(): bool
    {
        $meta = stream_get_meta_data($this->resource);
        return $meta['seekable'];
    }

    /** {@inheritdoc} */
    public function seek($offset, $whence = SEEK_SET): void
    {
        fseek($this->resource, $offset, $whence);
    }

    /** {@inheritdoc} */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /** {@inheritdoc} */
    public function isWritable(): bool
    {
        $meta = stream_get_meta_data($this->resource);
        return strpos($meta['mode'], 'w') !== false || strpos($meta['mode'], '+') !== false;
    }

    /** {@inheritdoc} */
    public function write($string): int
    {
        return fwrite($this->resource, $string);
    }

    /** {@inheritdoc} */
    public function isReadable(): bool
    {
        $meta = stream_get_meta_data($this->resource);
        return strpos($meta['mode'], 'r') !== false || strpos($meta['mode'], '+') !== false;
    }

    /** {@inheritdoc} */
    public function read($length): string
    {
        return fread($this->resource, $length);
    }

    /** {@inheritdoc} */
    public function getContents(): string
    {
        return stream_get_contents($this->resource);
    }

    /** {@inheritdoc} */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->resource);
        return $key ? ($meta[$key] ?? null) : $meta;
    }
}