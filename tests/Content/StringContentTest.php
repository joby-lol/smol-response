<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use PHPUnit\Framework\TestCase;

class StringContentTest extends TestCase
{

    public function test_creates_string_content(): void
    {
        $content = new StringContent('test content');

        $this->assertEquals('test content', $content->content);
        $this->assertEquals('page.html', $content->filename());
    }

    public function test_calculates_size(): void
    {
        $content = new StringContent('hello world');

        $this->assertEquals(11, $content->size());
    }

    public function test_empty_string_size(): void
    {
        $content = new StringContent('');

        $this->assertEquals(0, $content->size());
    }

    public function test_generates_etag(): void
    {
        $content = new StringContent('test content');

        $etag = $content->etag();
        $this->assertNotNull($etag);
        $this->assertEquals(md5('test content'), $etag);
    }

    public function test_same_content_generates_same_etag(): void
    {
        $content1 = new StringContent('identical');
        $content2 = new StringContent('identical');

        $this->assertEquals($content1->etag(), $content2->etag());
    }

    public function test_different_content_generates_different_etag(): void
    {
        $content1 = new StringContent('content1');
        $content2 = new StringContent('content2');

        $this->assertNotEquals($content1->etag(), $content2->etag());
    }

    public function test_renders_full_content(): void
    {
        $content = new StringContent('hello world');

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('hello world', $output);
    }

    public function test_renders_empty_content(): void
    {
        $content = new StringContent('');

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('', $output);
    }

    public function test_renders_range_from_start_to_end(): void
    {
        $content = new StringContent('0123456789');

        ob_start();
        $content->renderRange(2, 5);
        $output = ob_get_clean();

        $this->assertEquals('2345', $output);
    }

    public function test_renders_range_from_start_to_eof(): void
    {
        $content = new StringContent('0123456789');

        ob_start();
        $content->renderRange(5, null);
        $output = ob_get_clean();

        $this->assertEquals('56789', $output);
    }

    public function test_renders_range_last_n_bytes(): void
    {
        $content = new StringContent('0123456789');

        ob_start();
        $content->renderRange(null, 3);
        $output = ob_get_clean();

        $this->assertEquals('789', $output);
    }

    public function test_renders_range_first_byte(): void
    {
        $content = new StringContent('0123456789');

        ob_start();
        $content->renderRange(0, 0);
        $output = ob_get_clean();

        $this->assertEquals('0', $output);
    }

    public function test_renders_range_last_byte(): void
    {
        $content = new StringContent('0123456789');

        ob_start();
        $content->renderRange(9, 9);
        $output = ob_get_clean();

        $this->assertEquals('9', $output);
    }

    public function test_renders_range_entire_content_via_range(): void
    {
        $content = new StringContent('0123456789');

        ob_start();
        $content->renderRange(0, 9);
        $output = ob_get_clean();

        $this->assertEquals('0123456789', $output);
    }

    public function test_throws_exception_for_invalid_range_both_null(): void
    {
        $content = new StringContent('test');

        $this->expectException(ContentException::class);
        $this->expectExceptionMessage('Invalid range: - for content of size 4');

        $content->renderRange(null, null);
    }

    public function test_throws_exception_for_unsatisfiable_range_start_beyond_end(): void
    {
        $content = new StringContent('0123456789');

        $this->expectException(RangeUnsatisfiableException::class);

        $content->renderRange(15, 20);
    }

    public function test_throws_exception_for_unsatisfiable_range_negative_start(): void
    {
        $content = new StringContent('0123456789');

        $this->expectException(RangeUnsatisfiableException::class);

        $content->renderRange(-5, 5);
    }

    public function test_throws_exception_for_unsatisfiable_range_end_before_start(): void
    {
        $content = new StringContent('0123456789');

        $this->expectException(RangeUnsatisfiableException::class);

        $content->renderRange(5, 2);
    }

    public function test_verify_range_valid_start_to_end(): void
    {
        $content = new StringContent('0123456789');

        $this->assertTrue($content->verifyRange(2, 5));
    }

    public function test_verify_range_valid_start_to_eof(): void
    {
        $content = new StringContent('0123456789');

        $this->assertTrue($content->verifyRange(5, null));
    }

    public function test_verify_range_valid_last_n_bytes(): void
    {
        $content = new StringContent('0123456789');

        $this->assertTrue($content->verifyRange(null, 3));
    }

    public function test_verify_range_invalid_both_null(): void
    {
        $content = new StringContent('test');

        $this->assertFalse($content->verifyRange(null, null));
    }

    public function test_verify_range_invalid_start_beyond_size(): void
    {
        $content = new StringContent('0123456789');

        $this->assertFalse($content->verifyRange(15, null));
    }

    public function test_verify_range_invalid_end_before_start(): void
    {
        $content = new StringContent('0123456789');

        $this->assertFalse($content->verifyRange(5, 2));
    }

    public function test_verify_range_invalid_negative_start(): void
    {
        $content = new StringContent('0123456789');

        $this->assertFalse($content->verifyRange(-5, 5));
    }

    public function test_verify_range_invalid_zero_length_with_last_bytes(): void
    {
        $content = new StringContent('0123456789');

        $this->assertFalse($content->verifyRange(null, 0));
    }

    public function test_verify_range_empty_content(): void
    {
        $content = new StringContent('');

        $this->assertFalse($content->verifyRange(0, 0));
        $this->assertFalse($content->verifyRange(null, 1));
    }

    public function test_default_filename_is_page_html(): void
    {
        $content = new StringContent('test');

        $this->assertEquals('page.html', $content->filename());
    }

    public function test_can_override_filename(): void
    {
        $content = new StringContent('test');
        $content->setFilename('custom.json');

        $this->assertEquals('custom.json', $content->filename());
    }

    public function test_infers_mime_type_from_filename(): void
    {
        $content = new StringContent('test');
        $content->setFilename('test.json');

        $this->assertEquals('application/json', $content->mime());
    }

    public function test_content_type_includes_charset_for_text(): void
    {
        $content = new StringContent('<html></html>');
        $content->setFilename('page.html');

        $this->assertEquals('text/html; charset=UTF-8', $content->contentType());
    }

    public function test_content_type_includes_charset_for_json(): void
    {
        $content = new StringContent('{}');
        $content->setFilename('data.json');

        $this->assertEquals('application/json; charset=UTF-8', $content->contentType());
    }

    public function test_attachment_defaults_to_false(): void
    {
        $content = new StringContent('test');

        $this->assertFalse($content->attachment());
    }

    public function test_can_set_attachment(): void
    {
        $content = new StringContent('test');
        $content->setAttachment(true);

        $this->assertTrue($content->attachment());
    }

    public function test_handles_multibyte_characters(): void
    {
        $content = new StringContent('Hello 世界');

        $this->assertEquals(12, $content->size()); // UTF-8 byte count
    }

    public function test_renders_multibyte_characters(): void
    {
        $content = new StringContent('Hello 世界');

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('Hello 世界', $output);
    }

    public function test_last_modified_defaults_to_null(): void
    {
        $content = new StringContent('test');

        $this->assertNull($content->lastModified());
    }

    public function test_charset_defaults_to_utf8(): void
    {
        $content = new StringContent('test');

        $this->assertEquals('UTF-8', $content->charset());
    }

}
