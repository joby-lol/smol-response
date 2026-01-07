<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use Closure;

/**
 * Class for rendering content via a callback function.
 */
class CallbackContent extends AbstractContent
{

    protected Closure $callback;

    public function __construct(Closure|callable $callback)
    {
        if ($callback instanceof Closure) {
            $this->callback = $callback;
        }
        else {
            $this->callback = Closure::fromCallable($callback);
        }
    }

    public function render(): void
    {
        ($this->callback)();
    }

}
