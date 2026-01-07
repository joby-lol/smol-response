<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use Joby\Smol\Response\ResponseException;

/**
 * Exception indicating that an error occurred involving the content of a response. Generally this should lead to the user being given a 500 error response.
 */
class ContentException extends ResponseException {

}
