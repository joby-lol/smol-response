<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

/**
 * Abstract class for content that is capable of being rendered to satisfy a range request. Must be able to satisfy requests for:
 * - specific start/end byte ranges (i.e. "n-k" = the nth to kth byte)
 * - unbounded start ranges (i.e. "n-" = nth byte to end of file)
 * - n-byte end ranges (i.e. "-n" the last n bytes of the file)
 */
interface RangeContentInterface extends ContentInterface
{

    /**
     * Render a specific byte range of the content to the client. Matches the semantics of HTTP range requests, so:
     * - If both $start and $end are provided, render bytes $start to $end inclusive.
     * - If only $start is provided, render from byte $start to the end of the content.
     * - If only $end is provided, render the last $end bytes of the content.
     *
     * @param int|null $start The starting byte index (inclusive).start
     * @param int|null $end The ending byte index (inclusive).
     *
     * @return void
     *
     * @throws ContentException if the content cannot be rendered
     * @throws RangeUnsatisfiableException if the requested range is invalid or cannot be satisfied
     */
    public function renderRange(int|null $start, int|null $end): void;

    /**
     * Ensure that the requested byte range is valid and can be satisfied by the content. Matches the semantics of HTTP range requests, so:
     * - If both $start and $end are provided, render bytes $start to $end inclusive.
     * - If only $start is provided, render from byte $start to the end of the content.
     * - If only $end is provided, render the last $end bytes of the content.
     *
     * @param int|null $start The starting byte index (inclusive).start
     * @param int|null $end The ending byte index (inclusive).
     *
     * @return bool
     * @throws ContentException if something goes wrong checking the range, such as I/O errors when determining content length
     */
    public function verifyRange(int|null $start, int|null $end): bool;

    /**
     * Get the size of the content in bytes. This must be implemented to support range requests.
     *
     * @return int The size of the content in bytes.
     */
    public function size(): int;

}
