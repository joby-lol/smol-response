<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use PHPUnit\Framework\TestCase;

class CacheControlTest extends TestCase
{

    public function test_no_store_short_circuits_all_other_directives(): void
    {
        $cc = new CacheControl(
            no_store: true,
            public: true,
            must_revalidate: true,
            max_age: 3600,
            s_maxage: 7200,
            stale_while_revalidate: 86400,
            stale_if_error: 86400,
        );

        $this->assertEquals('no-store, max-age=0', (string) $cc);
    }

    public function test_public_with_all_options(): void
    {
        $cc = new CacheControl(
            no_store: false,
            public: true,
            must_revalidate: false,
            max_age: 300,
            s_maxage: 600,
            stale_while_revalidate: 86400,
            stale_if_error: 43200,
        );

        $expected = 'public, max-age=300, s-maxage=600, stale-while-revalidate=86400, stale-if-error=43200';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_private_with_must_revalidate(): void
    {
        $cc = new CacheControl(
            no_store: false,
            public: false,
            must_revalidate: true,
            max_age: 300,
            s_maxage: null,
            stale_while_revalidate: null,
            stale_if_error: null,
        );

        $this->assertEquals('private, must-revalidate, max-age=300', (string) $cc);
    }

    public function test_public_with_no_optional_directives(): void
    {
        $cc = new CacheControl(
            no_store: false,
            public: true,
            must_revalidate: false,
            max_age: null,
            s_maxage: null,
            stale_while_revalidate: null,
            stale_if_error: null,
        );

        $this->assertEquals('public', (string) $cc);
    }

    public function test_private_without_must_revalidate(): void
    {
        $cc = new CacheControl(
            no_store: false,
            public: false,
            must_revalidate: false,
            max_age: 600,
            s_maxage: null,
            stale_while_revalidate: null,
            stale_if_error: null,
        );

        $this->assertEquals('private, max-age=600', (string) $cc);
    }

    public function test_public_content_preset_with_defaults(): void
    {
        $cc = CacheControl::publicContent();

        $expected = 'public, max-age=300, s-maxage=300, stale-while-revalidate=86400, stale-if-error=86400';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_public_content_preset_with_custom_values(): void
    {
        $cc = CacheControl::publicContent(600, 3600);

        $expected = 'public, max-age=600, s-maxage=600, stale-while-revalidate=3600, stale-if-error=3600';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_public_media_preset_with_defaults(): void
    {
        $cc = CacheControl::publicMedia();

        $expected = 'public, max-age=31536000, s-maxage=31536000, stale-while-revalidate=31536000, stale-if-error=31536000';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_public_media_preset_with_custom_values(): void
    {
        $cc = CacheControl::publicMedia(86400, 604800);

        $expected = 'public, max-age=86400, s-maxage=86400, stale-while-revalidate=604800, stale-if-error=604800';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_private_content_preset_with_defaults(): void
    {
        $cc = CacheControl::privateContent();

        $expected = 'private, must-revalidate, max-age=300, s-maxage=300, stale-while-revalidate=600, stale-if-error=600';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_private_content_preset_with_custom_values(): void
    {
        $cc = CacheControl::privateContent(120, 240);

        $expected = 'private, must-revalidate, max-age=120, s-maxage=120, stale-while-revalidate=240, stale-if-error=240';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_private_media_preset_with_defaults(): void
    {
        $cc = CacheControl::privateMedia();

        $expected = 'private, max-age=31536000, s-maxage=31536000, stale-while-revalidate=31536000, stale-if-error=31536000';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_private_media_preset_with_custom_values(): void
    {
        $cc = CacheControl::privateMedia(3600, 7200);

        $expected = 'private, max-age=3600, s-maxage=3600, stale-while-revalidate=7200, stale-if-error=7200';
        $this->assertEquals($expected, (string) $cc);
    }

    public function test_never_cached_preset(): void
    {
        $cc = CacheControl::neverCached();

        $this->assertEquals('no-store, max-age=0', (string) $cc);
    }

    public function test_stringable_implementation(): void
    {
        $cc = CacheControl::publicContent();

        // Verify it implements Stringable and can be used in string contexts
        $this->assertInstanceOf(\Stringable::class, $cc);
        $header = "Cache-Control: " . $cc;
        $this->assertStringStartsWith('Cache-Control: public', $header);
    }

}
