<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

/**
 * Interface for content that supports HTTP range requests.
 *
 * Implementing this interface allows content to be served in partial chunks, which is essential for video streaming, large file downloads with resume capability, and efficient bandwidth usage.
 *
 * Range requests support three formats:
 * - Specific range: bytes n-k (from byte n to byte k inclusive)
 * - From position to end: bytes n- (from byte n to the end)
 * - Last N bytes: bytes -n (the last n bytes)
 */
interface RangeContentInterface extends ContentInterface
{

    /**
     * Render a specific byte range of the content.
     *
     * Matches HTTP range request semantics:
     * - Both provided: render bytes $start to $end inclusive (e.g., "0-1023" = first 1024 bytes)
     * - Only $start: render from byte $start to the end (e.g., "1000-" = byte 1000 to EOF)
     * - Only $end: render the last $end bytes (e.g., "-500" = last 500 bytes)
     *
     * @param int|null $start The starting byte position (0-indexed, inclusive)
     * @param int|null $end The ending byte position (0-indexed, inclusive)
     * @return void
     * @throws ContentException if the content cannot be rendered due to I/O errors
     * @throws RangeUnsatisfiableException if the requested range is invalid or cannot be satisfied
     */
    public function renderRange(int|null $start, int|null $end): void;

    /**
     * Check if a byte range is valid and satisfiable for this content.
     *
     * Validates that the requested range makes sense given the content's size and structure. Should return false for invalid ranges rather than throwing exceptions.
     *
     * @param int|null $start The starting byte position (0-indexed, inclusive)
     * @param int|null $end The ending byte position (0-indexed, inclusive)
     * @return bool True if the range is valid and can be satisfied, false otherwise
     * @throws ContentException if something goes wrong checking the range (e.g., I/O errors determining size)
     */
    public function verifyRange(int|null $start, int|null $end): bool;

    /**
     * Get the size of the content in bytes. This must be implemented to support range requests.
     *
     * @return int The size of the content in bytes.
     */
    public function size(): int;

}
