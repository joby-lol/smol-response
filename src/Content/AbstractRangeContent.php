<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

/**
 * Base class for content that supports HTTP range requests.
 *
 * Provides a default implementation of verifyRange() that handles the standard range request validation logic. Subclasses only need to implement renderRange() to support partial content delivery.
 */
abstract class AbstractRangeContent extends AbstractContent implements RangeContentInterface
{

    /**
     * Validate a byte range request.
     *
     * Checks that the range is valid given the content size and follows HTTP range semantics. Handles all three range formats: n-k, n-, and -n.
     *
     * @param int|null $start The starting byte position (0-indexed, inclusive)
     * @param int|null $end The ending byte position (0-indexed, inclusive)
     * @return bool True if the range is valid and satisfiable, false otherwise
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

    /**
     * Get the total size of the content in bytes.
     *
     * This implementation returns the protected $size property cast to int. Subclasses may override if size needs to be computed dynamically.
     *
     * @return int The content size in bytes
     */
    public function size(): int
    {
        return (int) $this->size;
    }

}
