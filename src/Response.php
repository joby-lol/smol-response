<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use Joby\Smol\Response\Content\ContentInterface;
use Joby\Smol\Response\Content\EmptyContent;
use Joby\Smol\Response\Content\FileContent;
use Joby\Smol\Response\Content\JsonContent;
use Joby\Smol\Response\Content\StringContent;
use Stringable;

/**
 * The main response builder for HTTP responses.
 *
 * This class provides a fluent interface for building HTTP responses with proper headers, cache control, content types, and status codes. Content can be provided as strings, arrays, or specialized content objects.
 */
class Response
{

    public Status $status;

    public Headers $headers;

    public CacheControl $cache;

    public ContentInterface $content;

    /**
     * Create a new HTTP response.
     *
     * Content will be automatically wrapped in the appropriate content type based on its type:
     * - Strings and Stringable objects become StringContent
     * - Arrays become JsonContent
     * - null becomes EmptyContent
     * - ContentInterface objects are used as-is
     *
     * @param int|Status $status HTTP status code or Status object (defaults to 200)
     * @param ContentInterface|string|Stringable|array<mixed>|null $content Response content
     * @param Headers|null $headers Custom headers (defaults to empty Headers collection)
     * @param CacheControl|null $cache Cache control settings (defaults to no caching)
     */
    public function __construct(
        int|Status $status = 200,
        ContentInterface|string|Stringable|array|null $content = null,
        Headers|null $headers = null,
        CacheControl|null $cache = null,
    )
    {
        $this->setStatus($status);
        $this->setContent($content);
        $this->headers = $headers ?? new Headers();
        $this->cache = $cache ?? CacheControl::neverCached();
    }

    /**
     * Create a redirect response.
     *
     * @param string|Stringable $url The URL to redirect to
     * @param bool $permanent Whether this is a permanent redirect (301/308) or temporary (302/307)
     * @param bool $preserve_method Whether to preserve the HTTP method (307/308) or allow method changes (301/302)
     * @return self A new Response configured as a redirect
     */
    public static function redirect(string|Stringable $url, bool $permanent = false, bool $preserve_method = false): self
    {
        $status_code = $permanent
            ? ($preserve_method ? 308 : 301)
            : ($preserve_method ? 307 : 302);
        $response = new self($status_code);
        $response->headers['Location'] = $url;
        return $response;
    }

    /**
     * Create a JSON response.
     *
     * The data will be automatically JSON-encoded and the Content-Type header will be set to application/json.
     *
     * @param mixed $data Any JSON-encodable data
     * @param int|Status $status HTTP status code (defaults to 200)
     * @return self A new Response containing the JSON data
     */
    public static function json(mixed $data, int|Status $status = 200): self
    {
        return new self(
            status: $status,
            content: new JsonContent($data),
        );
    }

    /**
     * Create a file response.
     *
     * The file will be served with automatic MIME type detection and support for range requests.
     *
     * @param string $file_path Path to the file to serve
     * @param int|Status $status HTTP status code (defaults to 200)
     * @return self A new Response configured to serve the file
     */
    public static function file(string $file_path, int|Status $status = 200): self
    {
        return new self(
            status: $status,
            content: new FileContent($file_path),
        );
    }

    /**
     * Set cache control for public HTML content.
     *
     * Configures caching suitable for public HTML pages that may update somewhat frequently, but can be cached briefly and served stale if needed.
     *
     * @return self This Response for method chaining
     */
    public function cachePublicContent(): self
    {
        $this->cache = CacheControl::publicContent();
        return $this;
    }

    /**
     * Set cache control for public static assets.
     *
     * Configures aggressive caching suitable for static assets like images, CSS, and JavaScript that rarely change and can be safely cached for long periods.
     *
     * @return self This Response for method chaining
     */
    public function cachePublicMedia(): self
    {
        $this->cache = CacheControl::publicMedia();
        return $this;
    }

    /**
     * Set cache control for private HTML content.
     *
     * Configures caching suitable for private HTML pages with user-specific content, allowing brief caching and limited stale serving.
     *
     * @return self This Response for method chaining
     */
    public function cachePrivateContent(): self
    {
        $this->cache = CacheControl::privateContent();
        return $this;
    }

    /**
     * Set cache control for private static assets.
     *
     * Configures caching suitable for user-specific static assets that can be cached for long periods but should not be shared between users.
     *
     * @return self This Response for method chaining
     */
    public function cachePrivateMedia(): self
    {
        $this->cache = CacheControl::privateMedia();
        return $this;
    }

    /**
     * Disable all caching for this response.
     *
     * Use this for content that must never be cached, such as CSRF tokens, CAPTCHAs, or other security-sensitive or highly dynamic content.
     *
     * @return self This Response for method chaining
     */
    public function cacheNever(): self
    {
        $this->cache = CacheControl::neverCached();
        return $this;
    }

    /**
     * Set the response status code.
     *
     * @param int|Status $status HTTP status code as an integer, or a Status object for custom reason phrases
     * @return self This Response for method chaining
     */
    public function setStatus(int|Status $status): self
    {
        if (is_int($status)) {
            $status = new Status($status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * Set the response content.
     *
     * Content will be automatically wrapped in the appropriate content type:
     * - Strings and Stringable objects become StringContent
     * - Arrays become JsonContent
     * - null becomes EmptyContent
     * - ContentInterface objects are used as-is
     *
     * @param ContentInterface|string|Stringable|array<mixed>|null $value The content to send
     * @return self This Response for method chaining
     */
    public function setContent(ContentInterface|string|Stringable|array|null $value): self
    {
        if ($value instanceof ContentInterface) {
            $this->content = $value;
        }
        elseif (is_string($value) || $value instanceof Stringable) {
            $this->content = new StringContent($value);
        }
        elseif (is_array($value)) {
            $this->content = new JsonContent($value);
        }
        else {
            $this->content = new EmptyContent();
        }
        return $this;
    }

}
