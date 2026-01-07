<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use DateTimeInterface;
use Stringable;

interface ContentInterface
{

    /**
     * The filename to suggest when downloading the content.
     */
    public function filename(): string|Stringable|null;

    /**
     * The MIME type of the content. May be inferred from the filename if not explicitly set.
     */
    public function mime(): string|Stringable|null;

    /**
     * The character set of the content, if applicable.
     */
    public function charset(): string|Stringable|null;

    /**
     * The content type of the content, if applicable, as it should be formatted for a Content-Type header. May include character set information if applicable.
     */
    public function contentType(): string|Stringable|null;

    /**
     * Whether the content should be delivered with a content disposition of "attachment" forcing a download.
     */
    public function attachment(): bool;

    /**
     * Etag for the content, if available/applicable. May be inferred automatically if not explicitly set, depending on the implementation.
     */
    public function etag(): string|Stringable|null;

    /**
     * Last-modified date for the content, if available/applicable. May be inferred automatically if not explicitly set, depending on the implementation.
     */
    public function lastModified(): DateTimeInterface|null;

    /**
     * Size of the content in bytes, if known/applicable.
     */
    public function size(): int|null;

    /**
     * Output the content to the client.
     */
    public function render(): void;

}
