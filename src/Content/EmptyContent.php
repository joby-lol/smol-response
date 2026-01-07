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
     * @inheritDoc
     */
    public function render(): void
    {
        // Intentionally left blank.
    }

    /**
     * @inheritDoc
     */
    public function attachment(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function etag(): string|Stringable|null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function filename(): string|Stringable|null
    {
        return 'empty.txt';
    }

    /**
     * @inheritDoc
     */
    public function lastModified(): \DateTimeInterface|null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mime(): string
    {
        return 'text/plain';
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'text/plain';
    }

    /**
     * @inheritDoc
     */
    public function charset(): null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function size(): int
    {
        return 0;
    }

}
