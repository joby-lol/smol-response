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

class Renderer
{

    /**
     * @codeCoverageIgnore this involves actual output to HTTP headers/body
     */
    public function render(Response $response): void
    {
        $this->prepareResponse($response);
        $this->renderHeaders($response);
        $this->renderBody($response);
    }

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
     * @return array<string|\Stringable|null>
     */
    public function buildHeaders(Response $response): array
    {
        $content = $response->content;
        // start with built-in header builders
        $headers = [];
        $headers['Content-Disposition'] = $this->header_contentDisposition($content);
        $headers['Content-Type'] = $content->contentType() ?: 'application/octet-stream';
        $headers['Cache-Control'] = (string) $response->cache;
        $headers['Last-Modified'] = $this->header_lastModified($content);
        $headers['ETag'] = $this->header_etag($content);
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

    public function header_lastModified(ContentInterface $content): string|null
    {
        return $content->lastModified()
            ? gmdate('D, d M Y H:i:s', $content->lastModified()->getTimestamp()) . ' GMT'
            : null;
    }

    public function header_etag(ContentInterface $content): string|null
    {
        return $content->etag()
            ? '"' . addcslashes((string) $content->etag(), '"\\') . '"'
            : null;
    }

}
