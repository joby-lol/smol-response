<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

/**
 * Wrapper that applies a byte range to range-capable content.
 *
 * This wraps RangeContentInterface implementations to serve partial content (HTTP 206). It passes through all metadata from the wrapped content but renders only the requested byte range. The Renderer automatically sets this wrapper when processing range requests.
 */
class AppliedRangeContent implements ContentInterface
{

    /**
     * Create a ranged content wrapper.
     *
     * @param RangeContentInterface $content The content to apply the range to
     * @param int|null $start Starting byte position (null for last N bytes)
     * @param int|null $end Ending byte position (null for "from start to end")
     * @throws RangeUnsatisfiableException if the range is invalid for the content
     */
    public function __construct(
        public readonly RangeContentInterface $content,
        public readonly int|null $start,
        public readonly int|null $end,
    )
    {
        if (!$this->content->verifyRange($this->start, $this->end)) {
            throw new RangeUnsatisfiableException("Invalid range: {$this->start}-{$this->end} for content of size {$this->content->size()}", $this->content->size());
        }
    }

    /**
     * @inheritDoc
     */
    public function attachment(): bool
    {
        return $this->content->attachment();
    }

    /**
     * @inheritDoc
     */
    public function charset(): string|\Stringable|null
    {
        return $this->content->charset();
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string|\Stringable|null
    {
        return $this->content->contentType();
    }

    /**
     * @inheritDoc
     */
    public function etag(): string|\Stringable|null
    {
        return $this->content->etag();
    }

    /**
     * @inheritDoc
     */
    public function filename(): string|\Stringable|null
    {
        return $this->content->filename();
    }

    /**
     * @inheritDoc
     */
    public function lastModified(): \DateTimeInterface|null
    {
        return $this->content->lastModified();
    }

    /**
     * @inheritDoc
     */
    public function mime(): string|\Stringable|null
    {
        return $this->content->mime();
    }

    /**
     * @inheritDoc
     */
    public function render(): void
    {
        $this->content->renderRange($this->start, $this->end);
    }

    /**
     * Generate the Content-Range header value.
     *
     * Builds a properly formatted Content-Range header showing which bytes are being sent and the total size of the content (e.g., "bytes 0-1023/4096").
     *
     * @return string The Content-Range header value
     */
    public function contentRangeHeader(): string
    {
        return "bytes {$this->startByte()}-{$this->endByte()}/{$this->content->size()}";
    }

    public function startByte(): int
    {
        // if there's an explicit start, use it
        if ($this->start !== null) {
            return $this->start;
        }
        // otherwise, calculate from the end
        elseif ($this->end !== null) {
            return $this->size() - $this->end;
        }
        // both null, shouldn't happen due to constructor check
        else {
            throw new RangeUnsatisfiableException("Both start and end are null", $this->size());
        }
    }

    public function endByte(): int
    {
        // if there's no end, it's the end of the content
        if ($this->end === null) {
            return $this->content->size() - 1;
        }
        elseif ($this->start === null) {
            // "-n" form, so end is the last byte
            return $this->content->size() - 1;
        }
        else {
            // "n-k" form, so use the provided end or last byte, whichever is first
            return min($this->end, $this->content->size() - 1);
        }
    }

    /**
     * @inheritDoc
     */
    public function size(): int
    {
        return $this->content->size();
    }

    /**
     * Get actual size that will be put out by render()
     */
    public function actualSize(): int
    {
        return $this->endByte() - $this->startByte() + 1;
    }

}
