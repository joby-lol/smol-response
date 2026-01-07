<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use Throwable;

/**
 * Exception thrown when a requested range is unsatisfiable. Should lead to the response having a 416 Range Not Satisfiable status, including the appropriate Content-Range header indicating the size of the resource.
 * 
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status/416
 */
class RangeUnsatisfiableException extends ContentException
{

    /**
     * The actual size of the requested resource
     * @var int
     */
    public readonly int $size;

    public function __construct(string $message, int $size, Throwable|null $previous = null)
    {
        parent::__construct($message, previous: $previous);
        $this->size = $size;
    }

}
