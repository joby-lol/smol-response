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
 * Content implementation for string-based content.
 */
class StringContent extends AbstractRangeContent
{
    public function __construct(
        public string $content,
    )
    {
        $this->filename = 'page.html';
    }

    /**
     * @inheritDoc
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
