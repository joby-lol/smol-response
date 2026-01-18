<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use Stringable;

/**
 * Cache control configuration for HTTP responses.
 *
 * Provides convenient presets for common caching scenarios and renders to proper Cache-Control header values. Use the static factory methods like publicContent(), publicMedia(), etc. for typical use cases.
 */
class CacheControl implements Stringable
{

    /**
     * Create a new cache control configuration.
     *
     * Typically you should use the static factory methods (publicContent(), publicMedia(), etc.) rather than constructing directly.
     *
     * @param bool $no_store If true, prevents any caching (overrides all other settings)
     * @param bool $public Whether the response can be cached by shared caches (CDNs, proxies)
     * @param bool $must_revalidate Whether caches must revalidate stale responses with the origin server
     * @param int|null $max_age Maximum time in seconds the response can be cached
     * @param int|null $s_maxage Maximum time in seconds for shared caches (overrides max_age for CDNs/proxies)
     * @param int|null $stale_while_revalidate Time in seconds stale content can be served while revalidating in background
     * @param int|null $stale_if_error Time in seconds stale content can be served if revalidation fails
     */
    public function __construct(
        public bool $no_store,
        public bool $public,
        public bool $must_revalidate,
        public int|null $max_age,
        public int|null $s_maxage,
        public int|null $stale_while_revalidate,
        public int|null $stale_if_error,
    ) {}

    /**
     * Preset for public HTML pages.
     *
     * Suitable for HTML content that may update somewhat frequently but can still be cached briefly, and may be served stale if needed for better performance.
     *
     * @param int $max_age How long (in seconds) to cache the content (default: 5 minutes)
     * @param int $max_stale_age How long (in seconds) stale content may be served (default: 24 hours)
     * @return self A configured CacheControl instance
     */
    public static function publicContent(int $max_age = 300, int $max_stale_age = 86400): self
    {
        return new self(
            false,
            true,
            false,
            $max_age,
            $max_age,
            $max_stale_age,
            $max_stale_age,
        );
    }

    /**
     * Preset for public static assets.
     *
     * Suitable for static content like images, CSS, JavaScript, and fonts that rarely change and can be aggressively cached for long periods.
     *
     * @param int $max_age How long (in seconds) to cache the content (default: 1 year)
     * @param int $max_stale_age How long (in seconds) stale content may be served (default: 1 year)
     * @return self A configured CacheControl instance
     */
    public static function publicMedia(int $max_age = 31536000, int $max_stale_age = 31536000): self
    {
        return new self(
            false,
            true,
            false,
            $max_age,
            $max_age,
            $max_stale_age,
            $max_stale_age,
        );
    }

    /**
     * Preset for private HTML pages.
     *
     * Suitable for user-specific HTML content that should only be cached in the user's browser, not in shared caches. Allows brief caching with limited stale serving.
     *
     * @param int $max_age How long (in seconds) to cache the content (default: 5 minutes)
     * @param int $max_stale_age How long (in seconds) stale content may be served (default: 10 minutes)
     * @return self A configured CacheControl instance
     */
    public static function privateContent(int $max_age = 300, int $max_stale_age = 600): self
    {
        return new self(
            false,
            false,
            true,
            $max_age,
            $max_age,
            $max_stale_age,
            $max_stale_age,
        );
    }

    /**
     * Preset for private static assets.
     *
     * Suitable for user-specific static content (like profile pictures) that should only be cached in the user's browser and not shared between users or cached by CDNs.
     *
     * @param int $max_age How long (in seconds) to cache the content (default: 1 year)
     * @param int $max_stale_age How long (in seconds) stale content may be served (default: 1 year)
     * @return self A configured CacheControl instance
     */
    public static function privateMedia(int $max_age = 31536000, int $max_stale_age = 31536000): self
    {
        return new self(
            false,
            false,
            false,
            $max_age,
            $max_age,
            $max_stale_age,
            $max_stale_age,
        );
    }

    /**
     * Preset for content that must never be cached.
     *
     * Use this for security-sensitive or highly dynamic content that must always be fresh, such as CSRF tokens, CAPTCHAs, or real-time data.
     *
     * @return self A configured CacheControl instance that prevents all caching
     */
    public static function neverCached(): self
    {
        return new self(
            true,
            false,
            false,
            null,
            null,
            null,
            null,
        );
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return implode(', ', $this->value());
    }

    /**
     * Generate the Cache-Control header directives as an array.
     *
     * This builds the individual cache directives based on the configured settings, which are then joined with commas when rendered to a header string.
     *
     * @return array<string> Array of cache directive strings
     */
    protected function value(): array
    {
        // no-store short-circuits everything else
        if ($this->no_store) {
            return ['no-store', 'max-age=0'];
        }
        // first and foremost, we need to always indicate whether or not we're public or private
        $value = [$this->public ? 'public' : 'private'];
        // if we're not public, we need to add must-revalidate
        if ($this->must_revalidate)
            $value[] = 'must-revalidate';
        // add max-age and s-maxage if they're set
        if ($this->max_age !== null)
            $value[] = 'max-age=' . $this->max_age;
        if ($this->s_maxage !== null)
            $value[] = 's-maxage=' . $this->s_maxage;
        // add stale-while-revalidate and stale-if-error if they're set
        if ($this->stale_while_revalidate !== null)
            $value[] = 'stale-while-revalidate=' . $this->stale_while_revalidate;
        if ($this->stale_if_error !== null)
            $value[] = 'stale-if-error=' . $this->stale_if_error;
        // return result
        return $value;
    }

}
