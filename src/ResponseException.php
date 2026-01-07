<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use RuntimeException;

/**
 * Class representing an exception specific to the Response component. Generally means that something has gone wrong or invalid data was provided. Generally should lead to the user being served a 500 Internal Server Error.
 */
class ResponseException extends RuntimeException
{

}
