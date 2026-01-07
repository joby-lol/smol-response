<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use PHPUnit\Framework\TestCase;

class EmptyContentTest extends TestCase
{

    public function test_renders_nothing(): void
    {
        $content = new EmptyContent();

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('', $output);
    }

    public function test_size_is_zero(): void
    {
        $content = new EmptyContent();

        $this->assertEquals(0, $content->size());
    }

    public function test_filename_is_empty_txt(): void
    {
        $content = new EmptyContent();

        $this->assertEquals('empty.txt', $content->filename());
    }

    public function test_mime_is_text_plain(): void
    {
        $content = new EmptyContent();

        $this->assertEquals('text/plain', $content->mime());
    }

    public function test_content_type_is_text_plain(): void
    {
        $content = new EmptyContent();

        $this->assertEquals('text/plain', $content->contentType());
    }

    public function test_charset_is_null(): void
    {
        $content = new EmptyContent();

        $this->assertNull($content->charset());
    }

    public function test_attachment_is_false(): void
    {
        $content = new EmptyContent();

        $this->assertFalse($content->attachment());
    }

    public function test_etag_is_null(): void
    {
        $content = new EmptyContent();

        $this->assertNull($content->etag());
    }

    public function test_last_modified_is_null(): void
    {
        $content = new EmptyContent();

        $this->assertNull($content->lastModified());
    }

    public function test_multiple_renders_produce_no_output(): void
    {
        $content = new EmptyContent();

        ob_start();
        $content->render();
        $content->render();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('', $output);
    }

    public function test_instances_are_equivalent(): void
    {
        $content1 = new EmptyContent();
        $content2 = new EmptyContent();

        $this->assertEquals($content1->size(), $content2->size());
        $this->assertEquals($content1->mime(), $content2->mime());
        $this->assertEquals($content1->filename(), $content2->filename());
        $this->assertEquals($content1->etag(), $content2->etag());
    }

}
