<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use ArrayAccess;
use Countable;
use Iterator;
use Stringable;

/**
 * @implements Iterator<string, string|Stringable|null>
 * @implements ArrayAccess<string, string|Stringable|null>
 */
class Headers implements ArrayAccess, Iterator, Countable
{

    /**
     * Internal list of header names and values. A value of null indicates the header should be removed when rendering, even overriding built-in headers.
     * @var array<string, string|Stringable|null>
     */
    protected array $headers = [];

    /**
     * @param array<string,string|Stringable|null> $headers
     */
    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
        ksort($this->headers);
    }

    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($this->normalizeHeaderName($offset), $this->headers);
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->headers[$this->normalizeHeaderName($offset)] ?? null;
    }

    /**
     * @param string|null $offset
     * @param string|Stringable|null $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            throw new ResponseException("Header name cannot be null");
        }
        $this->headers[$this->normalizeHeaderName($offset)] = $value;
        ksort($this->headers);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->headers[$this->normalizeHeaderName($offset)]);
    }

    /**
     * @return string|Stringable|null|false
     */
    public function current(): mixed
    {
        return current($this->headers);
    }

    public function next(): void
    {
        next($this->headers);
    }

    /**
     * @return string|null
     */
    public function key(): mixed
    {
        return key($this->headers);
    }

    public function valid(): bool
    {
        return key($this->headers) !== null;
    }

    public function rewind(): void
    {
        reset($this->headers);
    }

    public function count(): int
    {
        return count($this->headers);
    }

    public function normalizeHeaderName(string $name): string
    {
        $name = strtolower($name);
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '-', $name);
        return $name;
    }

}
