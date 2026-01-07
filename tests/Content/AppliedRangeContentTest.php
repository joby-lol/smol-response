<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use PHPUnit\Framework\TestCase;

class AppliedRangeContentTest extends TestCase
{

    public function test_creates_range_content_with_start_and_end(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 2, 5);

        $this->assertSame($content, $ranged->content);
        $this->assertEquals(2, $ranged->start);
        $this->assertEquals(5, $ranged->end);
    }

    public function test_creates_range_content_with_start_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 5, null);

        $this->assertEquals(5, $ranged->start);
        $this->assertNull($ranged->end);
    }

    public function test_creates_range_content_with_end_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, null, 3);

        $this->assertNull($ranged->start);
        $this->assertEquals(3, $ranged->end);
    }

    public function test_throws_exception_for_invalid_range(): void
    {
        $content = new StringContent('0123456789');

        $this->expectException(RangeUnsatisfiableException::class);

        new AppliedRangeContent($content, 20, 25);
    }

    public function test_throws_exception_for_both_null(): void
    {
        $content = new StringContent('0123456789');

        $this->expectException(RangeUnsatisfiableException::class);

        new AppliedRangeContent($content, null, null);
    }

    public function test_passes_through_size(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 2, 5);

        $this->assertEquals(10, $ranged->size());
    }

    public function test_passes_through_filename(): void
    {
        $content = new StringContent('test');
        $content->setFilename('test.txt');
        $ranged = new AppliedRangeContent($content, 0, 1);

        $this->assertEquals('test.txt', $ranged->filename());
    }

    public function test_passes_through_mime(): void
    {
        $content = new StringContent('test');
        $content->setFilename('test.html');
        $ranged = new AppliedRangeContent($content, 0, 1);

        $this->assertEquals('text/html', $ranged->mime());
    }

    public function test_passes_through_content_type(): void
    {
        $content = new StringContent('test');
        $content->setFilename('test.html');
        $ranged = new AppliedRangeContent($content, 0, 1);

        $this->assertEquals('text/html; charset=UTF-8', $ranged->contentType());
    }

    public function test_passes_through_charset(): void
    {
        $content = new StringContent('test');
        $ranged = new AppliedRangeContent($content, 0, 1);

        $this->assertEquals('UTF-8', $ranged->charset());
    }

    public function test_passes_through_attachment(): void
    {
        $content = new StringContent('test');
        $content->setAttachment(true);
        $ranged = new AppliedRangeContent($content, 0, 1);

        $this->assertTrue($ranged->attachment());
    }

    public function test_passes_through_etag(): void
    {
        $content = new StringContent('test');
        $ranged = new AppliedRangeContent($content, 0, 1);

        $this->assertEquals(md5('test'), $ranged->etag());
    }

    public function test_passes_through_last_modified(): void
    {
        $content = new StringContent('test');
        $ranged = new AppliedRangeContent($content, 0, 1);

        $this->assertNull($ranged->lastModified());
    }

    public function test_renders_calls_render_range(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 2, 5);

        ob_start();
        $ranged->render();
        $output = ob_get_clean();

        $this->assertEquals('2345', $output);
    }

    public function test_start_byte_with_explicit_start(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 3, 7);

        $this->assertEquals(3, $ranged->startByte());
    }

    public function test_start_byte_with_start_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 5, null);

        $this->assertEquals(5, $ranged->startByte());
    }

    public function test_start_byte_with_end_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, null, 3);

        // "-3" means last 3 bytes, starting at byte 7
        $this->assertEquals(7, $ranged->startByte());
    }

    public function test_end_byte_with_explicit_end(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 2, 5);

        $this->assertEquals(5, $ranged->endByte());
    }

    public function test_end_byte_with_start_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 5, null);

        // "5-" means from byte 5 to end, which is byte 9
        $this->assertEquals(9, $ranged->endByte());
    }

    public function test_end_byte_with_end_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, null, 3);

        // "-3" means last 3 bytes, ending at byte 9
        $this->assertEquals(9, $ranged->endByte());
    }

    public function test_content_range_header_with_start_and_end(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 2, 5);

        $this->assertEquals('bytes 2-5/10', $ranged->contentRangeHeader());
    }

    public function test_content_range_header_with_start_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 5, null);

        $this->assertEquals('bytes 5-9/10', $ranged->contentRangeHeader());
    }

    public function test_content_range_header_with_end_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, null, 3);

        $this->assertEquals('bytes 7-9/10', $ranged->contentRangeHeader());
    }

    public function test_actual_size_with_start_and_end(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 2, 5);

        // Bytes 2-5 inclusive = 4 bytes
        $this->assertEquals(4, $ranged->actualSize());
    }

    public function test_actual_size_with_start_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 5, null);

        // Bytes 5-9 inclusive = 5 bytes
        $this->assertEquals(5, $ranged->actualSize());
    }

    public function test_actual_size_with_end_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, null, 3);

        // Last 3 bytes = 3 bytes
        $this->assertEquals(3, $ranged->actualSize());
    }

    public function test_actual_size_first_byte_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 0, 0);

        $this->assertEquals(1, $ranged->actualSize());
    }

    public function test_actual_size_last_byte_only(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 9, 9);

        $this->assertEquals(1, $ranged->actualSize());
    }

    public function test_renders_first_byte(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 0, 0);

        ob_start();
        $ranged->render();
        $output = ob_get_clean();

        $this->assertEquals('0', $output);
    }

    public function test_renders_last_byte(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 9, 9);

        ob_start();
        $ranged->render();
        $output = ob_get_clean();

        $this->assertEquals('9', $output);
    }

    public function test_renders_entire_content_via_range(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 0, 9);

        ob_start();
        $ranged->render();
        $output = ob_get_clean();

        $this->assertEquals('0123456789', $output);
    }

    public function test_readonly_properties(): void
    {
        $content = new StringContent('test');
        $ranged = new AppliedRangeContent($content, 0, 1);

        $reflection = new \ReflectionClass($ranged);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_throws_exception_for_end_before_start(): void
    {
        $content = new StringContent('0123456789');

        $this->expectException(RangeUnsatisfiableException::class);

        new AppliedRangeContent($content, 5, 2);
    }

    public function test_throws_exception_for_start_beyond_size(): void
    {
        $content = new StringContent('0123456789');

        $this->expectException(RangeUnsatisfiableException::class);

        new AppliedRangeContent($content, 15, null);
    }

    public function test_exception_includes_size_in_message(): void
    {
        $content = new StringContent('0123456789');

        try {
            new AppliedRangeContent($content, 20, 25);
            $this->fail('Expected RangeUnsatisfiableException');
        }
        catch (RangeUnsatisfiableException $e) {
            $this->assertEquals(10, $e->size);
        }
    }

    public function test_works_with_different_range_content_types(): void
    {
        // Test that it works with any RangeContentInterface implementation
        $json = new StringContent('{"test":"data"}');
        $ranged = new AppliedRangeContent($json, 0, 5);

        ob_start();
        $ranged->render();
        $output = ob_get_clean();

        $this->assertEquals('{"test', $output);
    }

    public function test_content_range_header_format(): void
    {
        $content = new StringContent('0123456789');
        $ranged = new AppliedRangeContent($content, 2, 5);

        $header = $ranged->contentRangeHeader();

        $this->assertStringStartsWith('bytes ', $header);
        $this->assertStringContainsString('-', $header);
        $this->assertStringContainsString('/', $header);
        $this->assertStringEndsWith('/10', $header);
    }

}
