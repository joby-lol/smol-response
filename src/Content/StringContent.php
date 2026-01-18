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
 * Content implementation for string-based responses.
 *
 * Suitable for HTML, plain text, or any other string-based content. Supports range requests for efficient partial content delivery. The content type is automatically inferred from the filename if not explicitly set.
 */
class StringContent extends AbstractRangeContent
{

    /**
     * Create new string-based content.
     *
     * @param string|Stringable $content The text content to send
     * @param string|Stringable|null $filename Suggested filename for downloads (default: 'page.html')
     */
    public function __construct(
        public string|Stringable $content,
        string|Stringable|null $filename = 'page.html',
    )
    {
        $this->filename = $filename;
    }

    /**
     * Render a specific byte range of the string content.
     *
     * Uses substr() to efficiently extract and output the requested portion of the string.
     *
     * @param int|null $start The starting byte position (0-indexed, inclusive)
     * @param int|null $end The ending byte position (0-indexed, inclusive)
     * @return void
     * @throws RangeUnsatisfiableException if the range is invalid for this content
     * @throws ContentException if both start and end are null (should not occur due to validation)
     */
    public function renderRange(int|null $start, int|null $end): void
    {
        if (!$this->verifyRange($start, $end)) {
            throw new RangeUnsatisfiableException("Invalid range: {$start}-{$end} for content of size {$this->size()}", $this->size());
        }
        if ($start === null && $end !== null) {
            echo substr($this->content, -$end);
        }
        elseif ($start !== null && $end === null) {
            echo substr($this->content, $start);
        }
        elseif ($start !== null && $end !== null) {
            echo substr($this->content, $start, $end - $start + 1);
        }
        else {
            // both null, throw an exception, we actually shouldn't ever get here because verifyRange should catch it
            throw new ContentException("Invalid range: both start and end cannot be null");
        }
    }

    /**
     * @inheritDoc
     */
    public function render(): void
    {
        echo $this->content;
    }

    /**
     * @inheritDoc
     */
    public function size(): int
    {
        return strlen($this->content);
    }

    public function etag(): string|Stringable|null
    {
        return $this->etag
            ?? hash('md5', $this->content)
            ?: null;
    }

}
