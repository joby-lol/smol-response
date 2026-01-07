<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{

    public function test_creates_empty_headers(): void
    {
        $headers = new Headers();

        $this->assertCount(0, $headers);
    }

    public function test_creates_headers_from_array(): void
    {
        $headers = new Headers([
            'Content-Type'  => 'text/html',
            'Cache-Control' => 'no-cache',
        ]);

        $this->assertCount(2, $headers);
        $this->assertEquals('text/html', $headers['Content-Type']);
        $this->assertEquals('no-cache', $headers['Cache-Control']);
    }

    public function test_normalizes_header_names(): void
    {
        $headers = new Headers();

        $this->assertEquals('Content-Type', $headers->normalizeHeaderName('content-type'));
        $this->assertEquals('Content-Type', $headers->normalizeHeaderName('CONTENT-TYPE'));
        $this->assertEquals('Content-Type', $headers->normalizeHeaderName('CoNtEnT-tYpE'));
        $this->assertEquals('X-Custom-Header', $headers->normalizeHeaderName('x-custom-header'));
        $this->assertEquals('Cache-Control', $headers->normalizeHeaderName('cache-control'));
    }

    public function test_array_access_get(): void
    {
        $headers = new Headers(['Content-Type' => 'text/html']);

        $this->assertEquals('text/html', $headers['Content-Type']);
        $this->assertEquals('text/html', $headers['content-type']);
        $this->assertEquals('text/html', $headers['CONTENT-TYPE']);
        $this->assertNull($headers['Non-Existent']);
    }

    public function test_array_access_set(): void
    {
        $headers = new Headers();

        $headers['Content-Type'] = 'application/json';
        $this->assertEquals('application/json', $headers['Content-Type']);

        // Test normalization on set
        $headers['cache-control'] = 'max-age=3600';
        $this->assertEquals('max-age=3600', $headers['Cache-Control']);
    }

    public function test_array_access_exists(): void
    {
        $headers = new Headers(['Content-Type' => 'text/html']);

        $this->assertTrue(isset($headers['Content-Type']));
        $this->assertTrue(isset($headers['content-type']));
        $this->assertFalse(isset($headers['Non-Existent']));
    }

    public function test_array_access_unset(): void
    {
        $headers = new Headers(['Content-Type' => 'text/html']);

        $this->assertTrue(isset($headers['Content-Type']));
        unset($headers['Content-Type']);
        $this->assertFalse(isset($headers['Content-Type']));
    }

    public function test_array_access_set_null_offset_throws_exception(): void
    {
        $headers = new Headers();

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Header name cannot be null');

        $headers[] = 'value';
    }

    public function test_allows_null_values(): void
    {
        $headers = new Headers(['X-Remove-Me' => null]);

        $this->assertTrue(isset($headers['X-Remove-Me']));
        $this->assertNull($headers['X-Remove-Me']);
    }

    public function test_iterator_interface(): void
    {
        $headers = new Headers([
            'Content-Type'  => 'text/html',
            'Cache-Control' => 'no-cache',
            'X-Custom'      => 'value',
        ]);

        $iterated = [];
        foreach ($headers as $name => $value) {
            $iterated[$name] = $value;
        }

        $this->assertCount(3, $iterated);
        $this->assertEquals('no-cache', $iterated['Cache-Control']);
        $this->assertEquals('text/html', $iterated['Content-Type']);
        $this->assertEquals('value', $iterated['X-Custom']);
    }

    public function test_iterator_manual_operations(): void
    {
        $headers = new Headers([
            'Content-Type'  => 'text/html',
            'Cache-Control' => 'no-cache',
        ]);

        $headers->rewind();
        $this->assertTrue($headers->valid());
        $this->assertEquals('Cache-Control', $headers->key());
        $this->assertEquals('no-cache', $headers->current());

        $headers->next();
        $this->assertTrue($headers->valid());
        $this->assertEquals('Content-Type', $headers->key());
        $this->assertEquals('text/html', $headers->current());

        $headers->next();
        $this->assertFalse($headers->valid());
    }

    public function test_countable_interface(): void
    {
        $headers = new Headers();
        $this->assertCount(0, $headers);

        $headers['Content-Type'] = 'text/html';
        $this->assertCount(1, $headers);

        $headers['Cache-Control'] = 'no-cache';
        $this->assertCount(2, $headers);

        unset($headers['Content-Type']);
        $this->assertCount(1, $headers);
    }

    public function test_headers_are_sorted_alphabetically(): void
    {
        $headers = new Headers([
            'X-Zebra'      => 'last',
            'Content-Type' => 'middle',
            'Accept'       => 'first',
        ]);

        $keys = [];
        foreach ($headers as $key => $value) {
            $keys[] = $key;
        }

        $this->assertEquals(['Accept', 'Content-Type', 'X-Zebra'], $keys);
    }

    public function test_headers_maintain_sort_after_modification(): void
    {
        $headers = new Headers(['Z-Last' => 'value']);
        $headers['A-First'] = 'value';
        $headers['M-Middle'] = 'value';

        $keys = [];
        foreach ($headers as $key => $value) {
            $keys[] = $key;
        }

        $this->assertEquals(['A-First', 'M-Middle', 'Z-Last'], $keys);
    }

    public function test_accepts_stringable_values(): void
    {
        $stringable =

            new class implements \Stringable {

            public function __toString(): string
            {
                return 'stringable-value';
            }

            };

        $headers = new Headers(['X-Custom' => $stringable]);

        $this->assertInstanceOf(\Stringable::class, $headers['X-Custom']);
        $this->assertEquals('stringable-value', (string) $headers['X-Custom']);
    }

    public function test_empty_headers_iteration(): void
    {
        $headers = new Headers();

        $count = 0;
        foreach ($headers as $name => $value) {
            $count++;
        }

        $this->assertEquals(0, $count);
    }

    public function test_rewind_allows_multiple_iterations(): void
    {
        $headers = new Headers(['Content-Type' => 'text/html']);

        // First iteration
        $count1 = 0;
        foreach ($headers as $name => $value) {
            $count1++;
        }

        // Second iteration
        $count2 = 0;
        foreach ($headers as $name => $value) {
            $count2++;
        }

        $this->assertEquals(1, $count1);
        $this->assertEquals(1, $count2);
    }

}
