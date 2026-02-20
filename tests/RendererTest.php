<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use DateTime;
use DateTimeZone;
use Joby\Smol\Response\Content\AppliedRangeContent;
use Joby\Smol\Response\Content\EmptyContent;
use Joby\Smol\Response\Content\FileContent;
use Joby\Smol\Response\Content\JsonContent;
use Joby\Smol\Response\Content\StringContent;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{

    private Renderer $renderer;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->renderer = new Renderer();
        $this->tempDir = sys_get_temp_dir() . '/renderer_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
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

    public function test_prepare_response_sets_206_for_range_content(): void
    {
        $fileContent = new StringContent('0123456789');
        $rangedContent = new AppliedRangeContent($fileContent, 0, 4);
        $response = new Response(200, $rangedContent);

        $this->assertEquals(200, $response->status->code);

        $this->renderer->prepareResponse($response);

        $this->assertEquals(206, $response->status->code);
    }

    public function test_prepare_response_does_not_modify_non_range_content(): void
    {
        $response = new Response(200, 'Hello World');

        $this->renderer->prepareResponse($response);

        $this->assertEquals(200, $response->status->code);
    }

    public function test_build_headers_includes_content_type(): void
    {
        $response = new Response(200, 'Hello World');
        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('text/html; charset=UTF-8', $headers['Content-Type']);
    }

    public function test_build_headers_includes_cache_control(): void
    {
        $response = new Response(200, 'test');
        $response->cachePublicContent();

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertStringContainsString('public', $headers['Cache-Control']);
    }

    public function test_build_headers_includes_content_length_for_string(): void
    {
        $response = new Response(200, 'Hello World');
        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Content-Length', $headers);
        $this->assertEquals('11', $headers['Content-Length']);
    }

    public function test_build_headers_includes_content_length_for_file(): void
    {
        $path = $this->createTempFile('test content');
        $response = Response::file($path);

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Content-Length', $headers);
        $this->assertEquals('12', $headers['Content-Length']);
    }

    public function test_build_headers_omits_content_length_when_null(): void
    {
        $response = new Response(200, new EmptyContent());
        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayNotHasKey('Content-Length', $headers);
    }

    public function test_build_headers_includes_etag_for_file(): void
    {
        $path = $this->createTempFile('test content');
        $response = Response::file($path);

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Etag', $headers);
        $this->assertStringStartsWith('"', $headers['Etag']);
        $this->assertStringEndsWith('"', $headers['Etag']);
    }

    public function test_build_headers_includes_etag_for_string(): void
    {
        $response = new Response(200, 'test content');

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Etag', $headers);
        $expectedEtag = '"' . md5('test content') . '"';
        $this->assertEquals($expectedEtag, $headers['Etag']);
    }

    public function test_build_headers_omits_etag_when_null(): void
    {
        $response = new Response(200, new EmptyContent());
        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayNotHasKey('Etag', $headers);
    }

    public function test_build_headers_omits_last_modified_when_null(): void
    {
        $response = new Response(200, 'test');
        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayNotHasKey('Last-Modified', $headers);
    }

    public function test_build_headers_includes_accept_ranges_for_range_content(): void
    {
        $path = $this->createTempFile('test content');
        $response = Response::file($path);

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Accept-Ranges', $headers);
        $this->assertEquals('bytes', $headers['Accept-Ranges']);
    }

    public function test_build_headers_omits_accept_ranges_for_non_range_content(): void
    {
        $response = Response::json(['test' => 'data']);
        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayNotHasKey('Accept-Ranges', $headers);
    }

    public function test_build_headers_includes_content_range_for_applied_range(): void
    {
        $fileContent = new StringContent('0123456789');
        $rangedContent = new AppliedRangeContent($fileContent, 2, 5);
        $response = new Response(200, $rangedContent);

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Content-Range', $headers);
        $this->assertEquals('bytes 2-5/10', $headers['Content-Range']);
    }

    public function test_build_headers_uses_actual_size_for_applied_range(): void
    {
        $fileContent = new StringContent('0123456789');
        $rangedContent = new AppliedRangeContent($fileContent, 2, 5);
        $response = new Response(200, $rangedContent);

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Content-Length', $headers);
        $this->assertEquals('4', $headers['Content-Length']);
    }

    public function test_build_headers_merges_user_headers(): void
    {
        $response = new Response(200, 'test');
        $response->headers['X-Custom-Header'] = 'custom value';

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('X-Custom-Header', $headers);
        $this->assertEquals('custom value', $headers['X-Custom-Header']);
    }

    public function test_build_headers_user_headers_override_defaults(): void
    {
        $response = new Response(200, 'test');
        $response->headers['Content-Type'] = 'text/plain';

        $headers = $this->renderer->buildHeaders($response);

        $this->assertEquals('text/plain', $headers['Content-Type']);
    }

    public function test_build_headers_filters_null_values(): void
    {
        $response = new Response(200, new EmptyContent());
        $headers = $this->renderer->buildHeaders($response);

        foreach ($headers as $value) {
            $this->assertNotNull($value);
        }
    }

    public function test_header_content_disposition_inline_by_default(): void
    {
        $content = new StringContent('test');
        $content->setFilename('document.txt');

        $disposition = $this->renderer->header_contentDisposition($content);

        $this->assertStringStartsWith('inline', $disposition);
        $this->assertStringContainsString('filename="document.txt"', $disposition);
    }

    public function test_header_content_disposition_attachment_when_set(): void
    {
        $content = new StringContent('test');
        $content->setFilename('document.txt');
        $content->setAttachment(true);

        $disposition = $this->renderer->header_contentDisposition($content);

        $this->assertStringStartsWith('attachment', $disposition);
        $this->assertStringContainsString('filename="document.txt"', $disposition);
    }

    public function test_header_content_disposition_without_filename(): void
    {
        $content = new EmptyContent();

        $disposition = $this->renderer->header_contentDisposition($content);

        $this->assertEquals('inline; filename="empty.txt"', $disposition);
    }

    public function test_header_content_disposition_sanitizes_filename(): void
    {
        $content = new StringContent('test');
        $content->setFilename('file with spaces & special!.txt');

        $disposition = $this->renderer->header_contentDisposition($content);

        $this->assertStringContainsString('inline; filename="file with spaces _ special_.txt"; filename*=UTF-8\'\'file%20with%20spaces%20%26%20special%21.txt', $disposition);
    }

    public function test_header_content_disposition_includes_utf8_filename(): void
    {
        $content = new StringContent('test');
        $content->setFilename('文档.txt');

        $disposition = $this->renderer->header_contentDisposition($content);

        $this->assertStringContainsString('filename*=UTF-8\'\'', $disposition);
        $this->assertStringContainsString(rawurlencode('文档.txt'), $disposition);
    }

    public function test_header_content_disposition_escapes_quotes_in_filename(): void
    {
        $content = new StringContent('test');
        $content->setFilename('file"with"quotes.txt');

        $disposition = $this->renderer->header_contentDisposition($content);

        $this->assertStringContainsString('filename="file_with_quotes.txt"', $disposition);
    }

    public function test_header_last_modified_formats_correctly(): void
    {
        $content = new StringContent('test');
        $date = new DateTime('2024-01-15 12:30:45', new DateTimeZone('America/New_York'));

        // Use reflection to set lastModified since it's protected
        $reflection = new \ReflectionProperty($content, 'lastModified');
        $reflection->setValue($content, $date);

        $lastModified = $this->renderer->header_lastModified($content);

        $this->assertStringContainsString('GMT', $lastModified);
        $this->assertMatchesRegularExpression('/\w{3}, \d{2} \w{3} \d{4} \d{2}:\d{2}:\d{2} GMT/', $lastModified);
    }

    public function test_header_last_modified_returns_null_when_not_set(): void
    {
        $content = new StringContent('test');

        $lastModified = $this->renderer->header_lastModified($content);

        $this->assertNull($lastModified);
    }

    public function test_header_etag_wraps_in_quotes(): void
    {
        $content = new StringContent('test content');

        $etag = $this->renderer->header_etag($content);

        $this->assertStringStartsWith('"', $etag);
        $this->assertStringEndsWith('"', $etag);
        $this->assertEquals('"' . md5('test content') . '"', $etag);
    }

    public function test_header_etag_escapes_quotes(): void
    {
        $content = new StringContent('test');

        // Use reflection to set custom etag
        $reflection = new \ReflectionProperty($content, 'etag');
        $reflection->setValue($content, 'contains"quote');

        $etag = $this->renderer->header_etag($content);

        $this->assertStringContainsString('\"', $etag);
    }

    public function test_header_etag_returns_null_when_not_set(): void
    {
        $content = new EmptyContent();

        $etag = $this->renderer->header_etag($content);

        $this->assertNull($etag);
    }

    public function test_build_headers_defaults_to_octet_stream_when_no_content_type(): void
    {
        // Create a mock content that returns null for contentType
        $content =

            new class extends \Joby\Smol\Response\Content\AbstractContent {

            public function render(): void
            {
                // no-op
            }

            public function contentType(): string|null
            {
                return null;
            }

            };

        $response = new Response(200, $content);
        $headers = $this->renderer->buildHeaders($response);

        $this->assertEquals('application/octet-stream', $headers['Content-Type']);
    }

    public function test_build_headers_for_json_response(): void
    {
        $response = Response::json(['test' => 'data']);
        $headers = $this->renderer->buildHeaders($response);

        $this->assertEquals('application/json; charset=UTF-8', $headers['Content-Type']);
        $this->assertArrayNotHasKey('Cache-Control', $headers);
    }

    public function test_build_headers_for_redirect_response(): void
    {
        $response = Response::redirect('https://example.com');
        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Location', $headers);
        $this->assertEquals('https://example.com', $headers['Location']);
    }

    public function test_build_headers_includes_all_standard_headers(): void
    {
        $path = $this->createTempFile('test content');
        $response = Response::file($path);
        $response->cachePublicMedia();

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Content-Length', $headers);
        $this->assertArrayHasKey('Content-Disposition', $headers);
        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertArrayHasKey('Etag', $headers);
        $this->assertArrayHasKey('Accept-Ranges', $headers);
    }

    public function test_header_content_disposition_with_complex_unicode_filename(): void
    {
        $content = new StringContent('test');
        $content->setFilename('Документ файл 2024.pdf');

        $disposition = $this->renderer->header_contentDisposition($content);

        // Should have ASCII version
        $this->assertStringContainsString('filename=', $disposition);
        // Should have UTF-8 version
        $this->assertStringContainsString('filename*=UTF-8\'\'', $disposition);
    }

    public function test_build_headers_with_stringable_values(): void
    {
        $stringable =

            new class implements \Stringable {

            public function __toString(): string
            {
                return 'stringable-value';
            }

            };

        $response = new Response(200, 'test');
        $response->headers['X-Custom'] = $stringable;

        $headers = $this->renderer->buildHeaders($response);

        $this->assertArrayHasKey('X-Custom', $headers);
    }

}
