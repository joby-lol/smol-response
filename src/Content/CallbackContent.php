<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use Closure;
use Stringable;

/**
 * Content implementation for dynamic content generation via callbacks.
 *
 * Allows you to provide a function that will be called to generate the response content. Useful for template rendering, streaming output, or any dynamic content generation. The callback should echo its output directly.
 */
class CallbackContent extends AbstractContent
{

    protected Closure $callback;

    /**
     * Create new callback-based content.
     *
     * The callback will be invoked when the response is rendered and should echo its output.
     *
     * @param Closure|callable $callback Function to call to generate the content
     * @param string|Stringable|null $filename Suggested filename for downloads (default: 'page.html')
     */
    public function __construct(
        Closure|callable $callback,
        string|Stringable|null $filename = 'page.html',
    )
    {
        if ($callback instanceof Closure) {
            $this->callback = $callback;
        }
        else {
            $this->callback = Closure::fromCallable($callback);
        }
        $this->filename = $filename;
    }

    /**
     * Render the content by invoking the callback.
     *
     * The callback is expected to echo its output directly to the output buffer.
     *
     * @return void
     */
    public function render(): void
    {
        ($this->callback)();
    }

}
