<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use PHPUnit\Framework\TestCase;

class FileContentTest extends TestCase
{

    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/file_content_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }

    private function createTempFile(string $content, string $filename = 'test.txt'): string
    {
        $path = $this->tempDir . '/' . $filename;
        file_put_contents($path, $content);
        return $path;
    }

    public function test_creates_file_content_with_valid_file(): void
    {
        $path = $this->createTempFile('test content');
        $content = new FileContent($path);

        $this->assertEquals($path, $content->source_file);
    }

    public function test_throws_exception_for_nonexistent_file(): void
    {
        $this->expectException(ContentException::class);
        $this->expectExceptionMessage('File does not exist');

        new FileContent('/nonexistent/file.txt');
    }

    public function test_source_file_is_readonly(): void
    {
        $path = $this->createTempFile('test');
        $content = new FileContent($path);

        $reflection = new \ReflectionProperty($content, 'source_file');
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_calculates_file_size(): void
    {
        $path = $this->createTempFile('hello world');
        $content = new FileContent($path);

        $this->assertEquals(11, $content->size());
    }

    public function test_size_of_empty_file(): void
    {
        $path = $this->createTempFile('');
        $content = new FileContent($path);

        $this->assertEquals(0, $content->size());
    }

    public function test_filename_returns_basename(): void
    {
        $path = $this->createTempFile('test', 'example.txt');
        $content = new FileContent($path);

        $this->assertEquals('example.txt', $content->filename());
    }

    public function test_can_override_filename(): void
    {
        $path = $this->createTempFile('test', 'original.txt');
        $content = new FileContent($path);
        $content->setFilename('custom.txt');

        $this->assertEquals('custom.txt', $content->filename());
    }

    public function test_generates_etag_from_file_hash(): void
    {
        $path = $this->createTempFile('test content');
        $content = new FileContent($path);

        $expectedHash = md5_file($path);
        $this->assertEquals($expectedHash, $content->etag());
    }

    public function test_same_file_content_generates_same_etag(): void
    {
        $path1 = $this->createTempFile('identical', 'file1.txt');
        $path2 = $this->createTempFile('identical', 'file2.txt');

        $content1 = new FileContent($path1);
        $content2 = new FileContent($path2);

        $this->assertEquals($content1->etag(), $content2->etag());
    }

    public function test_different_file_content_generates_different_etag(): void
    {
        $path1 = $this->createTempFile('content1', 'file1.txt');
        $path2 = $this->createTempFile('content2', 'file2.txt');

        $content1 = new FileContent($path1);
        $content2 = new FileContent($path2);

        $this->assertNotEquals($content1->etag(), $content2->etag());
    }

    public function test_throws_exception_when_etag_hash_fails(): void
    {
        $path = $this->createTempFile('test content');
        $content = new FileContent($path);

        // Delete the file after construction to make md5_file fail
        unlink($path);

        $this->expectException(ContentException::class);
        $this->expectExceptionMessage('Failed to compute file hash for ETag');

        $content->etag();
    }

    public function test_infers_mime_type_from_filename(): void
    {
        $path = $this->createTempFile('{}', 'data.json');
        $content = new FileContent($path);

        $this->assertEquals('application/json', $content->mime());
    }

    public function test_renders_full_file(): void
    {
        $path = $this->createTempFile('hello world');
        $content = new FileContent($path);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('hello world', $output);
    }

    public function test_renders_empty_file(): void
    {
        $path = $this->createTempFile('');
        $content = new FileContent($path);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('', $output);
    }

    public function test_renders_large_file(): void
    {
        $largeContent = str_repeat('0123456789', 1000); // 10KB
        $path = $this->createTempFile($largeContent);
        $content = new FileContent($path);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals($largeContent, $output);
    }

    public function test_renders_range_from_start_to_end(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        ob_start();
        $content->renderRange(2, 5);
        $output = ob_get_clean();

        $this->assertEquals('2345', $output);
    }

    public function test_renders_range_from_start_to_eof(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        ob_start();
        $content->renderRange(5, null);
        $output = ob_get_clean();

        $this->assertEquals('56789', $output);
    }

    public function test_renders_range_last_n_bytes(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        ob_start();
        $content->renderRange(null, 3);
        $output = ob_get_clean();

        $this->assertEquals('789', $output);
    }

    public function test_renders_range_first_byte(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        ob_start();
        $content->renderRange(0, 0);
        $output = ob_get_clean();

        $this->assertEquals('0', $output);
    }

    public function test_renders_range_last_byte(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        ob_start();
        $content->renderRange(9, 9);
        $output = ob_get_clean();

        $this->assertEquals('9', $output);
    }

    public function test_renders_range_entire_file(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        ob_start();
        $content->renderRange(0, 9);
        $output = ob_get_clean();

        $this->assertEquals('0123456789', $output);
    }

    public function test_render_chunk_size_defaults_to_8192(): void
    {
        $path = $this->createTempFile('test');
        $content = new FileContent($path);

        $this->assertEquals(8192, $content->render_chunk_size);
    }

    public function test_can_modify_render_chunk_size(): void
    {
        $path = $this->createTempFile('test');
        $content = new FileContent($path);

        $content->render_chunk_size = 1024;

        $this->assertEquals(1024, $content->render_chunk_size);
    }

    public function test_renders_large_range_with_chunking(): void
    {
        $largeContent = str_repeat('abcdefghij', 2000); // 20KB
        $path = $this->createTempFile($largeContent);
        $content = new FileContent($path);
        $content->render_chunk_size = 1024; // Force chunking

        ob_start();
        $content->renderRange(0, 10239); // First 10KB
        $output = ob_get_clean();

        $this->assertEquals(10240, strlen($output));
        $this->assertEquals(substr($largeContent, 0, 10240), $output);
    }

    public function test_throws_exception_for_invalid_range_both_null(): void
    {
        $path = $this->createTempFile('test');
        $content = new FileContent($path);

        $this->expectException(ContentException::class);
        $this->expectExceptionMessage('Invalid range: - for content of size 4');

        $content->renderRange(null, null);
    }

    public function test_throws_exception_for_unsatisfiable_range(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        $this->expectException(RangeUnsatisfiableException::class);

        $content->renderRange(15, 20);
    }

    public function test_verify_range_valid_ranges(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        $this->assertTrue($content->verifyRange(2, 5));
        $this->assertTrue($content->verifyRange(5, null));
        $this->assertTrue($content->verifyRange(null, 3));
        $this->assertTrue($content->verifyRange(0, 9));
    }

    public function test_verify_range_invalid_ranges(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        $this->assertFalse($content->verifyRange(null, null));
        $this->assertFalse($content->verifyRange(15, null));
        $this->assertFalse($content->verifyRange(5, 2));
        $this->assertFalse($content->verifyRange(-5, 5));
    }

    public function test_content_type_includes_charset_for_text(): void
    {
        $path = $this->createTempFile('<html></html>', 'page.html');
        $content = new FileContent($path);

        $this->assertEquals('text/html; charset=UTF-8', $content->contentType());
    }

    public function test_attachment_defaults_to_false(): void
    {
        $path = $this->createTempFile('test');
        $content = new FileContent($path);

        $this->assertFalse($content->attachment());
    }

    public function test_can_set_attachment(): void
    {
        $path = $this->createTempFile('test');
        $content = new FileContent($path);
        $content->setAttachment(true);

        $this->assertTrue($content->attachment());
    }

    public function test_last_modified_defaults_to_null(): void
    {
        $path = $this->createTempFile('test');
        $content = new FileContent($path);

        $this->assertNull($content->lastModified());
    }

    public function test_charset_defaults_to_utf8(): void
    {
        $path = $this->createTempFile('test');
        $content = new FileContent($path);

        $this->assertEquals('UTF-8', $content->charset());
    }

    public function test_handles_binary_content(): void
    {
        $binaryData = pack('C*', 0, 1, 2, 3, 255, 254, 253);
        $path = $this->createTempFile($binaryData, 'data.bin');
        $content = new FileContent($path);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals($binaryData, $output);
    }

    public function test_range_with_binary_content(): void
    {
        $binaryData = pack('C*', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
        $path = $this->createTempFile($binaryData);
        $content = new FileContent($path);

        ob_start();
        $content->renderRange(3, 6);
        $output = ob_get_clean();

        $this->assertEquals(pack('C*', 3, 4, 5, 6), $output);
    }

    public function test_etag_caching(): void
    {
        $path = $this->createTempFile('test content');
        $content = new FileContent($path);

        $etag1 = $content->etag();
        $etag2 = $content->etag();

        // Should return the same cached value
        $this->assertSame($etag1, $etag2);
    }

    public function test_multiple_renders_produce_same_output(): void
    {
        $path = $this->createTempFile('test content');
        $content = new FileContent($path);

        ob_start();
        $content->render();
        $output1 = ob_get_clean();

        ob_start();
        $content->render();
        $output2 = ob_get_clean();

        $this->assertEquals($output1, $output2);
    }

    public function test_handles_paths_with_special_characters(): void
    {
        $filename = 'test file with spaces.txt';
        $path = $this->createTempFile('content', $filename);
        $content = new FileContent($path);

        $this->assertEquals($filename, $content->filename());
    }

    public function test_renders_file_at_various_offsets(): void
    {
        $path = $this->createTempFile('0123456789');
        $content = new FileContent($path);

        // Test various ranges
        $ranges = [
            [0, 2, '012'],
            [3, 5, '345'],
            [7, 9, '789'],
        ];

        foreach ($ranges as [$start, $end, $expected]) {
            ob_start();
            $content->renderRange($start, $end);
            $output = ob_get_clean();
            $this->assertEquals($expected, $output, "Range $start-$end failed");
        }
    }

}
