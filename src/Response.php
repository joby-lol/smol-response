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

class Response
{

    public Status $status;

    public Headers $headers;

    public CacheControl $cache;

    public ContentInterface $content;

    /**
     * @param int|Status $status
     * @param ContentInterface|string|Stringable|array<mixed>|null $content
     * @param Headers|null $headers
     * @param CacheControl|null $cache
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

    public static function redirect(string|Stringable $url, bool $permanent = false, bool $preserve_method = false): self
    {
        $status_code = $permanent
            ? ($preserve_method ? 308 : 301)
            : ($preserve_method ? 307 : 302);
        $response = new self($status_code);
        $response->headers['Location'] = $url;
        return $response;
    }

    public static function json(mixed $data, int|Status $status = 200): self
    {
        return new self(
            status: $status,
            content: new JsonContent($data),
        );
    }

    public static function file(string $file_path, int|Status $status = 200): self
    {
        return new self(
            status: $status,
            content: new FileContent($file_path),
        );
    }

    public function cachePublicContent(): self
    {
        $this->cache = CacheControl::publicContent();
        return $this;
    }

    public function cachePublicMedia(): self
    {
        $this->cache = CacheControl::publicMedia();
        return $this;
    }

    public function cachePrivateContent(): self
    {
        $this->cache = CacheControl::privateContent();
        return $this;
    }

    public function cachePrivateMedia(): self
    {
        $this->cache = CacheControl::privateMedia();
        return $this;
    }

    public function cacheNever(): self
    {
        $this->cache = CacheControl::neverCached();
        return $this;
    }

    /**
     * Set the response status using either a Status object or an integer.
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
     * Set the response content using a variety of input types.
     * 
     * @param ContentInterface|string|Stringable|array<mixed>|null $value
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
