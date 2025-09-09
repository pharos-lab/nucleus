<?php

namespace Nucleus\Http;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    protected $resource;

    public function __construct($content = '')
    {
        $this->resource = fopen('php://temp', 'r+');
        if ($content !== '') {
            fwrite($this->resource, $content);
            rewind($this->resource);
        }
    }

    public function __toString(): string
    {
        $this->seek(0);
        return stream_get_contents($this->resource);
    }

    public function close(): void { fclose($this->resource); }
    public function detach() { $res = $this->resource; $this->resource = null; return $res; }
    public function getSize(): ?int { $stats = fstat($this->resource); return $stats['size'] ?? null; }
    public function tell(): int { return ftell($this->resource); }
    public function eof(): bool { return feof($this->resource); }
    public function isSeekable(): bool { $meta = stream_get_meta_data($this->resource); return $meta['seekable']; }
    public function seek($offset, $whence = SEEK_SET): void { fseek($this->resource, $offset, $whence); }
    public function rewind(): void { $this->seek(0); }
    public function isWritable(): bool { $meta = stream_get_meta_data($this->resource); return strpos($meta['mode'], 'w') !== false || strpos($meta['mode'], '+') !== false; }
    public function write($string): int { return fwrite($this->resource, $string); }
    public function isReadable(): bool { $meta = stream_get_meta_data($this->resource); return strpos($meta['mode'], 'r') !== false || strpos($meta['mode'], '+') !== false; }
    public function read($length): string { return fread($this->resource, $length); }
    public function getContents(): string { return stream_get_contents($this->resource); }
    public function getMetadata($key = null) { $meta = stream_get_meta_data($this->resource); return $key ? ($meta[$key] ?? null) : $meta; }
}