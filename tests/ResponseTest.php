<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use Joby\Smol\Response\Content\EmptyContent;
use Joby\Smol\Response\Content\FileContent;
use Joby\Smol\Response\Content\JsonContent;
use Joby\Smol\Response\Content\StringContent;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{

    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/response_test_' . uniqid();
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

    public function test_creates_response_with_defaults(): void
    {
        $response = new Response();

        $this->assertEquals(200, $response->status->code);
        $this->assertInstanceOf(EmptyContent::class, $response->content);
        $this->assertInstanceOf(Headers::class, $response->headers);
        $this->assertInstanceOf(CacheControl::class, $response->cache);
    }

    public function test_creates_response_with_integer_status(): void
    {
        $response = new Response(404);

        $this->assertEquals(404, $response->status->code);
        $this->assertEquals('Not Found', $response->status->reason_phrase);
    }

    public function test_creates_response_with_status_object(): void
    {
        $status = new Status(201);
        $response = new Response($status);

        $this->assertSame($status, $response->status);
        $this->assertEquals(201, $response->status->code);
    }

    public function test_creates_response_with_string_content(): void
    {
        $response = new Response(200, 'Hello World');

        $this->assertInstanceOf(StringContent::class, $response->content);
        $this->assertEquals('Hello World', $response->content->content);
    }

    public function test_creates_response_with_stringable_content(): void
    {
        $stringable =

            new class implements \Stringable {

            public function __toString(): string
            {
                return 'Stringable Content';
            }

            };

        $response = new Response(200, $stringable);

        $this->assertInstanceOf(StringContent::class, $response->content);
    }

    public function test_creates_response_with_array_content(): void
    {
        $data = ['key' => 'value'];
        $response = new Response(200, $data);

        $this->assertInstanceOf(JsonContent::class, $response->content);
        $this->assertEquals($data, $response->content->data);
    }

    public function test_creates_response_with_null_content(): void
    {
        $response = new Response(200, null);

        $this->assertInstanceOf(EmptyContent::class, $response->content);
    }

    public function test_creates_response_with_content_object(): void
    {
        $content = new StringContent('test');
        $response = new Response(200, $content);

        $this->assertSame($content, $response->content);
    }

    public function test_creates_response_with_custom_headers(): void
    {
        $headers = new Headers(['X-Custom' => 'value']);
        $response = new Response(200, null, $headers);

        $this->assertSame($headers, $response->headers);
        $this->assertEquals('value', $response->headers['X-Custom']);
    }

    public function test_creates_response_with_custom_cache_control(): void
    {
        $cache = CacheControl::publicContent();
        $response = new Response(200, null, null, $cache);

        $this->assertSame($cache, $response->cache);
    }

    public function test_default_cache_is_never_cached(): void
    {
        $response = new Response();

        $this->assertEquals('no-store, max-age=0', (string) $response->cache);
    }

    public function test_set_status_with_integer(): void
    {
        $response = new Response();
        $response->setStatus(404);

        $this->assertEquals(404, $response->status->code);
        $this->assertEquals('Not Found', $response->status->reason_phrase);
    }

    public function test_set_status_with_status_object(): void
    {
        $response = new Response();
        $status = new Status(500);
        $response->setStatus($status);

        $this->assertSame($status, $response->status);
    }

    public function test_set_status_returns_self(): void
    {
        $response = new Response();
        $result = $response->setStatus(201);

        $this->assertSame($response, $result);
    }

    public function test_set_content_with_string(): void
    {
        $response = new Response();
        $response->setContent('Test Content');

        $this->assertInstanceOf(StringContent::class, $response->content);
        $this->assertEquals('Test Content', $response->content->content);
    }

    public function test_set_content_with_array(): void
    {
        $response = new Response();
        $data = ['test' => 'data'];
        $response->setContent($data);

        $this->assertInstanceOf(JsonContent::class, $response->content);
        $this->assertEquals($data, $response->content->data);
    }

    public function test_set_content_with_null(): void
    {
        $response = new Response(200, 'initial');
        $response->setContent(null);

        $this->assertInstanceOf(EmptyContent::class, $response->content);
    }

    public function test_set_content_with_content_object(): void
    {
        $response = new Response();
        $content = new JsonContent(['data' => 'value']);
        $response->setContent($content);

        $this->assertSame($content, $response->content);
    }

    public function test_set_content_returns_self(): void
    {
        $response = new Response();
        $result = $response->setContent('test');

        $this->assertSame($response, $result);
    }

    public function test_redirect_factory_temporary_default(): void
    {
        $response = Response::redirect('https://example.com');

        $this->assertEquals(302, $response->status->code);
        $this->assertEquals('https://example.com', $response->headers['Location']);
    }

    public function test_redirect_factory_permanent(): void
    {
        $response = Response::redirect('https://example.com', permanent: true);

        $this->assertEquals(301, $response->status->code);
        $this->assertEquals('https://example.com', $response->headers['Location']);
    }

    public function test_redirect_factory_preserve_method_temporary(): void
    {
        $response = Response::redirect('https://example.com', preserve_method: true);

        $this->assertEquals(307, $response->status->code);
    }

    public function test_redirect_factory_preserve_method_permanent(): void
    {
        $response = Response::redirect('https://example.com', permanent: true, preserve_method: true);

        $this->assertEquals(308, $response->status->code);
    }

    public function test_redirect_factory_with_stringable(): void
    {
        $stringable =

            new class implements \Stringable {

            public function __toString(): string
            {
                return 'https://example.com/stringable';
            }

            };

        $response = Response::redirect($stringable);

        $this->assertEquals('https://example.com/stringable', (string) $response->headers['Location']);
    }

    public function test_json_factory_with_defaults(): void
    {
        $data = ['key' => 'value'];
        $response = Response::json($data);

        $this->assertEquals(200, $response->status->code);
        $this->assertInstanceOf(JsonContent::class, $response->content);
        $this->assertEquals($data, $response->content->data);
    }

    public function test_json_factory_with_custom_status(): void
    {
        $response = Response::json(['error' => 'not found'], 404);

        $this->assertEquals(404, $response->status->code);
        $this->assertInstanceOf(JsonContent::class, $response->content);
    }

    public function test_json_factory_with_status_object(): void
    {
        $status = new Status(201);
        $response = Response::json(['created' => true], $status);

        $this->assertEquals(201, $response->status->code);
    }

    public function test_file_factory_with_defaults(): void
    {
        $path = $this->createTempFile('test content');
        $response = Response::file($path);

        $this->assertEquals(200, $response->status->code);
        $this->assertInstanceOf(FileContent::class, $response->content);
        $this->assertEquals($path, $response->content->source_file);
    }

    public function test_file_factory_with_custom_status(): void
    {
        $path = $this->createTempFile('test content');
        $response = Response::file($path, 206);

        $this->assertEquals(206, $response->status->code);
        $this->assertInstanceOf(FileContent::class, $response->content);
    }

    public function test_cache_public_content(): void
    {
        $response = new Response();
        $result = $response->cachePublicContent();

        $this->assertSame($response, $result);
        $this->assertStringContainsString('public', (string) $response->cache);
        $this->assertStringContainsString('max-age=300', (string) $response->cache);
    }

    public function test_cache_public_media(): void
    {
        $response = new Response();
        $result = $response->cachePublicMedia();

        $this->assertSame($response, $result);
        $this->assertStringContainsString('public', (string) $response->cache);
        $this->assertStringContainsString('max-age=31536000', (string) $response->cache);
    }

    public function test_cache_private_content(): void
    {
        $response = new Response();
        $result = $response->cachePrivateContent();

        $this->assertSame($response, $result);
        $this->assertStringContainsString('private', (string) $response->cache);
        $this->assertStringContainsString('must-revalidate', (string) $response->cache);
    }

    public function test_cache_private_media(): void
    {
        $response = new Response();
        $result = $response->cachePrivateMedia();

        $this->assertSame($response, $result);
        $this->assertStringContainsString('private', (string) $response->cache);
        $this->assertStringContainsString('max-age=31536000', (string) $response->cache);
    }

    public function test_cache_never(): void
    {
        $response = new Response();
        $response->cachePublicContent(); // Set something else first
        $result = $response->cacheNever();

        $this->assertSame($response, $result);
        $this->assertEquals('no-store, max-age=0', (string) $response->cache);
    }

    public function test_fluent_interface_chaining(): void
    {
        $response = (new Response())
            ->setStatus(201)
            ->setContent('Created')
            ->cachePublicContent();

        $this->assertEquals(201, $response->status->code);
        $this->assertInstanceOf(StringContent::class, $response->content);
        $this->assertStringContainsString('public', (string) $response->cache);
    }

    public function test_headers_are_mutable(): void
    {
        $response = new Response();
        $response->headers['X-Custom'] = 'value';

        $this->assertEquals('value', $response->headers['X-Custom']);
    }

    public function test_cache_is_mutable(): void
    {
        $response = new Response();
        $response->cache = CacheControl::publicMedia();

        $this->assertStringContainsString('max-age=31536000', (string) $response->cache);
    }

    public function test_status_is_mutable(): void
    {
        $response = new Response();
        $response->status = new Status(500);

        $this->assertEquals(500, $response->status->code);
    }

    public function test_content_is_mutable(): void
    {
        $response = new Response();
        $response->content = new JsonContent(['test' => 'data']);

        $this->assertInstanceOf(JsonContent::class, $response->content);
    }

    public function test_redirect_creates_empty_content(): void
    {
        $response = Response::redirect('https://example.com');

        $this->assertInstanceOf(EmptyContent::class, $response->content);
    }

    public function test_redirect_sets_location_header(): void
    {
        $response = Response::redirect('https://example.com/path?query=value');

        $this->assertTrue(isset($response->headers['Location']));
        $this->assertEquals('https://example.com/path?query=value', $response->headers['Location']);
    }

    public function test_all_redirect_status_codes(): void
    {
        // 301 - Permanent, no method preservation
        $r301 = Response::redirect('/path', permanent: true, preserve_method: false);
        $this->assertEquals(301, $r301->status->code);

        // 302 - Temporary, no method preservation (default)
        $r302 = Response::redirect('/path');
        $this->assertEquals(302, $r302->status->code);

        // 307 - Temporary, with method preservation
        $r307 = Response::redirect('/path', preserve_method: true);
        $this->assertEquals(307, $r307->status->code);

        // 308 - Permanent, with method preservation
        $r308 = Response::redirect('/path', permanent: true, preserve_method: true);
        $this->assertEquals(308, $r308->status->code);
    }

    public function test_json_factory_accepts_various_data_types(): void
    {
        $r1 = Response::json(['array' => 'data']);
        $this->assertInstanceOf(JsonContent::class, $r1->content);

        $r2 = Response::json('string');
        $this->assertInstanceOf(JsonContent::class, $r2->content);

        $r3 = Response::json(123);
        $this->assertInstanceOf(JsonContent::class, $r3->content);

        $r4 = Response::json(null);
        $this->assertInstanceOf(JsonContent::class, $r4->content);
    }

    public function test_empty_array_creates_json_content(): void
    {
        $response = new Response(200, []);

        $this->assertInstanceOf(JsonContent::class, $response->content);
        $this->assertEquals([], $response->content->data);
    }

    public function test_complex_construction(): void
    {
        $headers = new Headers(['X-Custom' => 'value']);
        $cache = CacheControl::publicContent();

        $response = new Response(
            status: 201,
            content: 'Created',
            headers: $headers,
            cache: $cache,
        );

        $this->assertEquals(201, $response->status->code);
        $this->assertSame($headers, $response->headers);
        $this->assertSame($cache, $response->cache);
        $this->assertInstanceOf(StringContent::class, $response->content);
    }

}
