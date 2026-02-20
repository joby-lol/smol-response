<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use Stringable;

/**
 * Maximally efficient representation of empty content. Does nothing. Stores no content. Has a size of zero. Renders nothing to the client.
 */
class EmptyContent implements ContentInterface
{

    /**
     * Empty content renders nothing
     */
    public function render(): void
    {
        // Intentionally left blank.
    }

    /**
     * Empty content is not an attachment
     */
    public function attachment(): bool
    {
        return false;
    }

    /**
     * Empty content has no Etag
     */
    public function etag(): string|Stringable|null
    {
        return null;
    }

    /**
     * Empty content is named empty.txt
     */
    public function filename(): string|Stringable|null
    {
        return 'empty.txt';
    }

    /**
     * Empty content is text/plain
     */
    public function lastModified(): \DateTimeInterface|null
    {
        return null;
    }

    /**
     * Empty content is text/plain
     */
    public function mime(): string
    {
        return 'text/plain';
    }

    /**
     * Enoty content us text/plain
     */
    public function contentType(): string
    {
        return 'text/plain';
    }

    /**
     * Empty content has no explicit character set.
     * 
     * @return null
     */
    public function charset(): string|null
    {
        return null;
    }

    /**
     * Empty content is size 0
     */
    public function size(): int
    {
        return 0;
    }

}
