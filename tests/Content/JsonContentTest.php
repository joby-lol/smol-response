<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use PHPUnit\Framework\TestCase;

class JsonContentTest extends TestCase
{

    public function test_creates_json_content(): void
    {
        $data = ['key' => 'value'];
        $content = new JsonContent($data);

        $this->assertSame($data, $content->data);
    }

    public function test_default_mime_type_is_application_json(): void
    {
        $content = new JsonContent(['test' => 'data']);

        $this->assertEquals('application/json', $content->mime());
    }

    public function test_default_filename_is_data_json(): void
    {
        $content = new JsonContent(['test' => 'data']);

        $this->assertEquals('data.json', $content->filename());
    }

    public function test_content_type_includes_charset(): void
    {
        $content = new JsonContent(['test' => 'data']);

        $this->assertEquals('application/json; charset=UTF-8', $content->contentType());
    }

    public function test_renders_array(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $content = new JsonContent($data);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('{"name":"John","age":30}', $output);
    }

    public function test_renders_nested_array(): void
    {
        $data = [
            'user' => [
                'name'    => 'Jane',
                'address' => [
                    'city' => 'New York',
                ],
            ],
        ];
        $content = new JsonContent($data);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertEquals('Jane', $decoded['user']['name']);
        $this->assertEquals('New York', $decoded['user']['address']['city']);
    }

    public function test_renders_string(): void
    {
        $content = new JsonContent('simple string');

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('"simple string"', $output);
    }

    public function test_renders_integer(): void
    {
        $content = new JsonContent(42);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('42', $output);
    }

    public function test_renders_float(): void
    {
        $content = new JsonContent(3.14);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('3.14', $output);
    }

    public function test_renders_boolean_true(): void
    {
        $content = new JsonContent(true);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('true', $output);
    }

    public function test_renders_boolean_false(): void
    {
        $content = new JsonContent(false);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('false', $output);
    }

    public function test_renders_null(): void
    {
        $content = new JsonContent(null);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('null', $output);
    }

    public function test_renders_empty_array(): void
    {
        $content = new JsonContent([]);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('[]', $output);
    }

    public function test_renders_empty_object(): void
    {
        $content = new JsonContent(new \stdClass());

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('{}', $output);
    }

    public function test_renders_numeric_array(): void
    {
        $content = new JsonContent([1, 2, 3, 4, 5]);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('[1,2,3,4,5]', $output);
    }

    public function test_handles_special_characters(): void
    {
        $data = ['text' => 'Hello "World" & <tags>'];
        $content = new JsonContent($data);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertEquals('Hello "World" & <tags>', $decoded['text']);
    }

    public function test_handles_unicode_characters(): void
    {
        $data = ['text' => 'Hello 世界 🌍'];
        $content = new JsonContent($data);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertEquals('Hello 世界 🌍', $decoded['text']);
    }

    public function test_handles_newlines_and_tabs(): void
    {
        $data = ['text' => "Line 1\nLine 2\tTabbed"];
        $content = new JsonContent($data);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertEquals("Line 1\nLine 2\tTabbed", $decoded['text']);
    }

    public function test_throws_exception_for_invalid_json(): void
    {
        // Create a resource, which cannot be JSON encoded
        $resource = fopen('php://memory', 'r');
        $content = new JsonContent(['resource' => $resource]);

        $this->expectException(\JsonException::class);

        ob_start();
        try {
            $content->render();
        }
        finally {
            ob_end_clean();
            fclose($resource);
        }
    }

    public function test_can_override_filename(): void
    {
        $content = new JsonContent(['test' => 'data']);
        $content->setFilename('custom.json');

        $this->assertEquals('custom.json', $content->filename());
    }

    public function test_attachment_defaults_to_false(): void
    {
        $content = new JsonContent(['test' => 'data']);

        $this->assertFalse($content->attachment());
    }

    public function test_can_set_attachment(): void
    {
        $content = new JsonContent(['test' => 'data']);
        $content->setAttachment(true);

        $this->assertTrue($content->attachment());
    }

    public function test_etag_defaults_to_null(): void
    {
        $content = new JsonContent(['test' => 'data']);

        $this->assertNull($content->etag());
    }

    public function test_last_modified_defaults_to_null(): void
    {
        $content = new JsonContent(['test' => 'data']);

        $this->assertNull($content->lastModified());
    }

    public function test_size_defaults_to_null(): void
    {
        $content = new JsonContent(['test' => 'data']);

        $this->assertNull($content->size());
    }

    public function test_charset_is_utf8(): void
    {
        $content = new JsonContent(['test' => 'data']);

        $this->assertEquals('UTF-8', $content->charset());
    }

    public function test_renders_object_with_public_properties(): void
    {
        $obj = new \stdClass();
        $obj->name = 'Test';
        $obj->value = 123;

        $content = new JsonContent($obj);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertEquals('Test', $decoded['name']);
        $this->assertEquals(123, $decoded['value']);
    }

    public function test_renders_associative_array_as_object(): void
    {
        $content = new JsonContent(['key' => 'value']);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('{', $output);
        $this->assertStringContainsString('"key"', $output);
    }

    public function test_data_is_public_and_mutable(): void
    {
        $content = new JsonContent(['initial' => 'data']);

        $content->data = ['modified' => 'data'];

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('{"modified":"data"}', $output);
    }

}
