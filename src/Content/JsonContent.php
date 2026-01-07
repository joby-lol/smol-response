<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

class JsonContent extends AbstractContent
{

    public function __construct(
        public mixed $data,
    )
    {
        $this->mime = "application/json";
        $this->filename = "data.json";
    }

    public function render(): void
    {
        echo json_encode($this->data, JSON_THROW_ON_ERROR);
    }

}
