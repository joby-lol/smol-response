<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

abstract class AbstractRangeContent extends AbstractContent implements RangeContentInterface
{

    /**
     * @inheritDoc
     */
    function verifyRange(int|null $start, int|null $end): bool
    {
        // one has to be provided
        if ($start === null && $end === null) {
            return false;
        }
        // now we might be need the size
        $size = $this->size();
        if ($size === 0) {
            return false;
        }
        if ($start === null) {
            // "-n" form (the last n bytes)
            // Valid as long as n > 0, and if n > size we just return the whole content.
            return $end > 0;
        }
        elseif ($end === null) {
            // "n-" form (from byte n to end)
            // invalid if start is outside the file bounds
            return $start >= 0
                && $start < $size;
        }
        else {
            // "n-k" form (between byte n and k inclusive)
            // valid if within bounds and end >= start
            return $start >= 0
                && $end >= $start
                && $end < $size;
        }
    }

    public function size(): int
    {
        return (int) $this->size;
    }

}
