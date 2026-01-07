<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{

    public function test_creates_status_with_default_reason_phrase(): void
    {
        $status = new Status(200);

        $this->assertEquals(200, $status->code);
        $this->assertEquals('OK', $status->reason_phrase);
    }

    public function test_creates_status_with_custom_reason_phrase(): void
    {
        $status = new Status(200, 'Everything is Fine');

        $this->assertEquals(200, $status->code);
        $this->assertEquals('Everything is Fine', $status->reason_phrase);
    }

    public function test_throws_exception_for_invalid_status_code(): void
    {
        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage('Invalid status code');

        new Status(999);
    }

    public function test_informational_status_codes(): void
    {
        $status100 = new Status(100);
        $this->assertEquals('Continue', $status100->reason_phrase);

        $status101 = new Status(101);
        $this->assertEquals('Switching Protocols', $status101->reason_phrase);
    }

    public function test_successful_status_codes(): void
    {
        $status200 = new Status(200);
        $this->assertEquals('OK', $status200->reason_phrase);

        $status201 = new Status(201);
        $this->assertEquals('Created', $status201->reason_phrase);

        $status204 = new Status(204);
        $this->assertEquals('No Content', $status204->reason_phrase);

        $status206 = new Status(206);
        $this->assertEquals('Partial Content', $status206->reason_phrase);
    }

    public function test_redirection_status_codes(): void
    {
        $status301 = new Status(301);
        $this->assertEquals('Moved Permanently', $status301->reason_phrase);

        $status302 = new Status(302);
        $this->assertEquals('Found', $status302->reason_phrase);

        $status304 = new Status(304);
        $this->assertEquals('Not Modified', $status304->reason_phrase);

        $status308 = new Status(308);
        $this->assertEquals('Permanent Redirect', $status308->reason_phrase);
    }

    public function test_client_error_status_codes(): void
    {
        $status400 = new Status(400);
        $this->assertEquals('Bad Request', $status400->reason_phrase);

        $status401 = new Status(401);
        $this->assertEquals('Unauthorized', $status401->reason_phrase);

        $status403 = new Status(403);
        $this->assertEquals('Forbidden', $status403->reason_phrase);

        $status404 = new Status(404);
        $this->assertEquals('Not Found', $status404->reason_phrase);

        $status429 = new Status(429);
        $this->assertEquals('Too Many Requests', $status429->reason_phrase);
    }

    public function test_server_error_status_codes(): void
    {
        $status500 = new Status(500);
        $this->assertEquals('Internal Server Error', $status500->reason_phrase);

        $status502 = new Status(502);
        $this->assertEquals('Bad Gateway', $status502->reason_phrase);

        $status503 = new Status(503);
        $this->assertEquals('Service Unavailable', $status503->reason_phrase);

        $status504 = new Status(504);
        $this->assertEquals('Gateway Timeout', $status504->reason_phrase);
    }

    public function test_uncommon_status_codes(): void
    {
        $status451 = new Status(451);
        $this->assertEquals('Unavailable For Legal Reasons', $status451->reason_phrase);

        $status506 = new Status(506);
        $this->assertEquals('Variant Also Negotiates', $status506->reason_phrase);

        $status511 = new Status(511);
        $this->assertEquals('Network Authentication Required', $status511->reason_phrase);
    }

    public function test_readonly_properties(): void
    {
        $status = new Status(200);

        // Verify the class is readonly by checking properties cannot be modified
        $reflection = new \ReflectionClass($status);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_custom_reason_phrase_overrides_default(): void
    {
        $status = new Status(404, 'Page Not Here');

        $this->assertEquals(404, $status->code);
        $this->assertEquals('Page Not Here', $status->reason_phrase);
        $this->assertNotEquals('Not Found', $status->reason_phrase);
    }

    public function test_codes_constant_contains_expected_entries(): void
    {
        $this->assertArrayHasKey(200, Status::CODES);
        $this->assertArrayHasKey(404, Status::CODES);
        $this->assertArrayHasKey(500, Status::CODES);

        $this->assertEquals('OK', Status::CODES[200]);
        $this->assertEquals('Not Found', Status::CODES[404]);
        $this->assertEquals('Internal Server Error', Status::CODES[500]);
    }

}
