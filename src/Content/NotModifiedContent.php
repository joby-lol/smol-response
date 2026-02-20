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

/**
 * Content type for stripping the metadata out of a given Content object and discarding its actual content. Used for generating 304 Not Modified and similar responses.
 */
class NotModifiedContent implements ContentInterface
{

    protected bool $attachment;

    protected string|Stringable|null $etag;

    protected string|Stringable|null $filename;

    protected DateTimeInterface|null $last_modified;

    protected string|Stringable|null $mime;

    protected string|Stringable|null $content_type;

    protected string|Stringable|null $charset;

    protected int|null $size;

    public function __construct(ContentInterface $existing_content)
    {
        $this->attachment = $existing_content->attachment();
        $this->etag = $existing_content->etag();
        $this->filename = $existing_content->filename();
        $this->last_modified = $existing_content->lastModified();
        $this->mime = $existing_content->mime();
        $this->content_type = $existing_content->contentType();
        $this->charset = $existing_content->charset();
        $this->size = $existing_content->size();
    }

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
        return $this->attachment;
    }

    /**
     * @inheritDoc
     */
    public function etag(): string|Stringable|null
    {
        return $this->etag;
    }

    /**
     * @inheritDoc
     */
    public function filename(): string|Stringable|null
    {
        return $this->filename;
    }

    /**
     * @inheritDoc
     */
    public function lastModified(): DateTimeInterface|null
    {
        return $this->last_modified;
    }

    /**
     * @inheritDoc
     */
    public function mime(): string|Stringable|null
    {
        return $this->mime;
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string|Stringable|null
    {
        return $this->content_type;
    }

    /**
     * @inheritDoc
     */
    public function charset(): string|Stringable|null
    {
        return $this->charset;
    }

    /**
     * @inheritDoc
     */
    public function size(): int|null
    {
        return $this->size;
    }

}
