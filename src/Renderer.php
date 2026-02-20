<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response;

use Joby\Smol\Response\Content\AppliedRangeContent;
use Joby\Smol\Response\Content\ContentInterface;
use Joby\Smol\Response\Content\RangeContentInterface;

/**
 * Handles rendering of HTTP responses to the client.
 *
 * This class is responsible for:
 * - Preparing responses (e.g., setting appropriate status codes for ranges)
 * - Building and sending HTTP headers with automatic metadata
 * - Rendering response content to the output stream
 */
class Renderer
{

    /**
     * Render the complete HTTP response to the client.
     *
     * This is the main entry point for sending a response. It prepares the response, sends all HTTP headers, and outputs the response body.
     *
     * @param Response $response The response to render
     * @codeCoverageIgnore this involves actual output to HTTP headers/body
     */
    public function render(Response $response): void
    {
        $this->prepareResponse($response);
        $this->renderHeaders($response);
        $this->renderBody($response);
    }

    /**
     * Prepare the response before rendering.
     *
     * Performs any necessary modifications to the response, such as setting status code to 206 for partial content responses.
     *
     * @param Response $response The response to prepare
     */
    public function prepareResponse(Response $response): void
    {
        // set status to 206 if content is an AppliedRangeContent
        if ($response->content instanceof AppliedRangeContent) {
            $response->setStatus(206);
        }
    }

    /**
     * @codeCoverageIgnore this involves actual output to HTTP headers
     */
    public function renderHeaders(Response $response): void
    {
        http_response_code($response->status->code);
        foreach ($this->buildHeaders($response) as $header => $value) {
            header($header . ': ' . $value, true);
        }
    }

    /**
     * @codeCoverageIgnore this involves actual output to HTTP body
     */
    public function renderBody(Response $response): void
    {
        $response->content->render();
    }

    /**
     * Build the complete set of HTTP headers for the response.
     *
     * Automatically generates headers based on content metadata (Content-Type, Content-Length,
     * Etag, Last-Modified, etc.) and merges with user-defined headers. User-defined headers
     * take precedence and can override generated headers.
     *
     * @param Response $response The response to build headers for
     * @return array<string|\Stringable|null> Associative array of header names to values
     */
    public function buildHeaders(Response $response): array
    {
        $content = $response->content;
        // start with built-in header builders
        $headers = [];
        $headers['Content-Disposition'] = $this->header_contentDisposition($content);
        $headers['Content-Type'] = $content->contentType() ?: 'application/octet-stream';
        if ($response->cache)
            $headers['Cache-Control'] = (string) $response->cache;
        $headers['Last-Modified'] = $this->header_lastModified($content);
        $headers['Etag'] = $this->header_etag($content);
        // special case for RangeContentInterface
        if ($content instanceof RangeContentInterface) {
            $headers['Accept-Ranges'] = 'bytes';
        }
        // special cases depending on whether this is an AppliedRangeContent content type
        if ($content instanceof AppliedRangeContent) {
            $headers['Content-Range'] = $content->contentRangeHeader();
            $headers['Content-Length'] = (string) $content->actualSize();
            $headers['Accept-Ranges'] = 'bytes';
        }
        else {
            $size = $content->size();
            if ($size !== null) {
                $headers['Content-Length'] = (string) $size;
            }
        }
        // merge in user-defined headers
        foreach ($response->headers as $header => $value) {
            $headers[$header] = $value;
        }
        // filter and sort
        $headers = array_filter($headers);
        return $headers;
    }

    /**
     * Generate a Content-Disposition header value.
     *
     * Creates a properly formatted header indicating whether the content should be displayed inline or downloaded as an attachment, including filename with proper encoding for both ASCII and UTF-8 filenames.
     *
     * @param ContentInterface $content The content to generate the header for
     * @return string The Content-Disposition header value
     */
    public function header_contentDisposition(ContentInterface $content): string
    {
        $value = $content->attachment() ? 'attachment' : 'inline';
        $filename = $content->filename();
        if ($filename === null) {
            return $value;
        }
        $filename = (string) $filename;
        $ascii_filename = mb_ereg_replace('[^a-zA-Z0-9_\- \.]', '_', $filename);
        assert(is_string($ascii_filename));
        $value .= '; filename="' . addcslashes($ascii_filename, '"\\') . '"';
        if ($ascii_filename != $filename) {
            $value .= '; filename*=UTF-8\'\'' . rawurlencode($filename);
        }
        return $value;
    }

    /**
     * Generate a Last-Modified header value.
     *
     * Formats the content's last modified timestamp as an HTTP-date string per RFC 7231.
     *
     * @param ContentInterface $content The content to generate the header for
     * @return string|null The Last-Modified header value, or null if not available
     */
    public function header_lastModified(ContentInterface $content): string|null
    {
        return $content->lastModified()
            ? gmdate('D, d M Y H:i:s', $content->lastModified()->getTimestamp()) . ' GMT'
            : null;
    }

    /**
     * Generate an Etag header value.
     *
     * Wraps the content's Etag value in quotes and properly escapes any special characters for use in an HTTP header.
     *
     * @param ContentInterface $content The content to generate the header for
     * @return string|null The Etag header value, or null if not available
     */
    public function header_etag(ContentInterface $content): string|null
    {
        return $content->etag()
            ? '"' . addcslashes((string) $content->etag(), '"\\') . '"'
            : null;
    }

}
