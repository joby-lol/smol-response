<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use PHPUnit\Framework\TestCase;

class CallbackContentTest extends TestCase
{

    public function test_creates_with_closure(): void
    {
        $content = new CallbackContent(function () {
            echo 'test output';
        });

        $this->assertInstanceOf(CallbackContent::class, $content);
    }

    public function test_renders_closure_output(): void
    {
        $content = new CallbackContent(function () {
            echo 'Hello World';
        });

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('Hello World', $output);
    }

    public function test_accepts_string_function_name(): void
    {
        $content = new CallbackContent('phpversion');

        $this->assertInstanceOf(CallbackContent::class, $content);
    }

    public function test_accepts_array_callable(): void
    {
        $obj =

            new class {

            public function output()
            {
                echo 'method output';
            }

            };

        $content = new CallbackContent([$obj, 'output']);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('method output', $output);
    }

    public function test_accepts_invokable_object(): void
    {
        $invokable =

            new class {

            public function __invoke()
            {
                echo 'invokable output';
            }

            };

        $content = new CallbackContent($invokable);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('invokable output', $output);
    }

    public function test_multiple_renders_work(): void
    {
        $counter = 0;
        $content = new CallbackContent(function () use (&$counter) {
            $counter++;
            echo "Render #$counter";
        });

        ob_start();
        $content->render();
        $output1 = ob_get_clean();

        ob_start();
        $content->render();
        $output2 = ob_get_clean();

        $this->assertEquals('Render #1', $output1);
        $this->assertEquals('Render #2', $output2);
    }

    public function test_can_set_filename(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });
        $content->setFilename('custom.txt');

        $this->assertEquals('custom.txt', $content->filename());
    }

    public function test_can_set_attachment(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });
        $content->setAttachment(true);

        $this->assertTrue($content->attachment());
    }

    public function test_attachment_defaults_to_false(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });

        $this->assertFalse($content->attachment());
    }

    public function test_infers_mime_from_filename(): void
    {
        $content = new CallbackContent(function () {
            echo '{"test": "data"}';
        });
        $content->setFilename('data.json');

        $this->assertEquals('application/json', $content->mime());
    }

    public function test_content_type_includes_charset(): void
    {
        $content = new CallbackContent(function () {
            echo '<html></html>';
        });
        $content->setFilename('page.html');

        $this->assertEquals('text/html; charset=UTF-8', $content->contentType());
    }

    public function test_charset_defaults_to_utf8(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });

        $this->assertEquals('UTF-8', $content->charset());
    }

    public function test_size_defaults_to_null(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });

        $this->assertNull($content->size());
    }

    public function test_etag_defaults_to_null(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });

        $this->assertNull($content->etag());
    }

    public function test_last_modified_defaults_to_null(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });

        $this->assertNull($content->lastModified());
    }

    public function test_callback_with_complex_output(): void
    {
        $data = ['item1', 'item2', 'item3'];
        $content = new CallbackContent(function () use ($data) {
            foreach ($data as $item) {
                echo $item . "\n";
            }
        });

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals("item1\nitem2\nitem3\n", $output);
    }

    public function test_callback_can_use_external_variables(): void
    {
        $message = 'External Variable';
        $content = new CallbackContent(function () use ($message) {
            echo $message;
        });

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('External Variable', $output);
    }

    public function test_subclass_can_pass_data_to_callback(): void
    {
        $subclass =

            new class ('test-value') extends CallbackContent {

            public function __construct(
                public readonly string $customProperty,
            )
            {
                parent::__construct(fn() => $this->renderContent());
            }

            private function renderContent(): void
            {
                echo 'Custom: ' . $this->customProperty;
            }

            };

        ob_start();
        $subclass->render();
        $output = ob_get_clean();

        $this->assertEquals('Custom: test-value', $output);
    }

    public function test_callback_with_no_output(): void
    {
        $content = new CallbackContent(function () {
            // No output
        });

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('', $output);
    }

    public function test_callback_can_echo_html(): void
    {
        $content = new CallbackContent(function () {
            echo '<h1>Title</h1>';
            echo '<p>Paragraph</p>';
        });

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('<h1>Title</h1><p>Paragraph</p>', $output);
    }

    public function test_callback_with_return_value_is_ignored(): void
    {
        $content = new CallbackContent(function () {
            echo 'echoed';
            return 'returned';
        });

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('echoed', $output);
    }

    public function test_static_method_callable(): void
    {
        $content = new CallbackContent([self::class, 'staticOutputMethod']);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('static method output', $output);
    }

    public static function staticOutputMethod(): void
    {
        echo 'static method output';
    }

    public function test_closure_from_callable_conversion(): void
    {
        $func = function () {
            echo 'converted';
        };

        $content = new CallbackContent($func);

        ob_start();
        $content->render();
        $output = ob_get_clean();

        $this->assertEquals('converted', $output);
    }

    public function test_filename_defaults_to_null(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });

        $this->assertNull($content->filename());
    }

    public function test_callback_can_access_external_content_object(): void
    {
        $content = new CallbackContent(function () {
            echo 'test';
        });
        $content->setFilename('example.txt');

        $wrapper = new CallbackContent(function () use ($content) {
            echo 'Filename: ' . $content->filename();
        });

        ob_start();
        $wrapper->render();
        $output = ob_get_clean();

        $this->assertEquals('Filename: example.txt', $output);
    }

}
