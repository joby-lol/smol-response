<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use Stringable;

class CacheControl implements Stringable
{

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
     * Preset for public HTML pages that may update more frequently, but should still be cached for a little while, and
     * may be served stale quite permissively if necessary.
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
     * Preset for public static content that should be cached for a long time, and may be served stale under a great
     * many circumstances.
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
     * Preset for HTML pages that are private, but may be served stale if necessary.
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
     * Preset for static content that should be cached for a long time, and may be served stale, but is nevertheless
     * private content.
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
     * Preset for content that should never be cached, such as CSRF forms, CAPTCHA, etc.
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
     * Generate the internal value as an array to be rendered.
     * 
     * @return array<string>
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
