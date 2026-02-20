<?php

namespace Joby\Smol\Response\Content;

use DateTime;
use PHPUnit\Framework\TestCase;

class NotModifiedContentTest extends TestCase
{

    protected function make_mock_content(array $overrides = []): ContentInterface
    {
        $mock = $this->createMock(ContentInterface::class);
        $defaults = [
            'attachment'   => false,
            'etag'         => null,
            'filename'     => null,
            'lastModified' => null,
            'mime'         => null,
            'contentType'  => null,
            'charset'      => null,
            'size'         => null,
        ];
        foreach (array_merge($defaults, $overrides) as $method => $value) {
            $mock->method($method)->willReturn($value);
        }
        return $mock;
    }

    public function test_metadata_is_copied_from_existing_content(): void
    {
        $last_modified = new DateTime('2026-01-01');
        $mock = $this->make_mock_content([
            'attachment'   => true,
            'etag'         => 'abc123',
            'filename'     => 'file.txt',
            'lastModified' => $last_modified,
            'mime'         => 'text/plain',
            'contentType'  => 'text/plain; charset=utf-8',
            'charset'      => 'utf-8',
            'size'         => 42,
        ]);

        $content = new NotModifiedContent($mock);

        $this->assertTrue($content->attachment());
        $this->assertSame('abc123', $content->etag());
        $this->assertSame('file.txt', $content->filename());
        $this->assertSame($last_modified, $content->lastModified());
        $this->assertSame('text/plain', $content->mime());
        $this->assertSame('text/plain; charset=utf-8', $content->contentType());
        $this->assertSame('utf-8', $content->charset());
        $this->assertSame(42, $content->size());
    }

    public function test_null_metadata_is_preserved(): void
    {
        $content = new NotModifiedContent($this->make_mock_content());

        $this->assertFalse($content->attachment());
        $this->assertNull($content->etag());
        $this->assertNull($content->filename());
        $this->assertNull($content->lastModified());
        $this->assertNull($content->mime());
        $this->assertNull($content->contentType());
        $this->assertNull($content->charset());
        $this->assertNull($content->size());
    }

    public function test_render_produces_no_output(): void
    {
        $content = new NotModifiedContent($this->make_mock_content());
        ob_start();
        $content->render();
        $output = ob_get_clean();
        $this->assertSame('', $output);
    }

    public function test_stringable_values_are_preserved(): void
    {
        $stringable =

            new class implements \Stringable {

            public function __toString(): string
            {
                return 'stringable-value';
            }

            };

        $mock = $this->make_mock_content([
            'etag'        => $stringable,
            'filename'    => $stringable,
            'mime'        => $stringable,
            'contentType' => $stringable,
            'charset'     => $stringable,
        ]);

        $content = new NotModifiedContent($mock);

        $this->assertSame($stringable, $content->etag());
        $this->assertSame($stringable, $content->filename());
        $this->assertSame($stringable, $content->mime());
        $this->assertSame($stringable, $content->contentType());
        $this->assertSame($stringable, $content->charset());
    }

}
