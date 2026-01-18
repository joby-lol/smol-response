<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

/**
 * Content implementation for JSON responses.
 *
 * Automatically encodes data as JSON with proper Content-Type headers. Throws an exception if the data cannot be JSON-encoded.
 */
class JsonContent extends AbstractContent
{

    /**
     * Create new JSON content.
     *
     * @param mixed $data Any JSON-encodable data (arrays, objects, scalars, etc.)
     */
    public function __construct(
        public mixed $data,
    )
    {
        $this->mime = "application/json";
        $this->filename = "data.json";
    }

    /**
     * Render the data as JSON.
     *
     * Encodes the data as JSON and outputs it. Throws an exception if the data cannot be encoded.
     *
     * @return void
     * @throws \JsonException if the data cannot be JSON-encoded
     */
    public function render(): void
    {
        echo json_encode($this->data, JSON_THROW_ON_ERROR);
    }

}
