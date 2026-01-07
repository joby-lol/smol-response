<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use DateTimeInterface;
use Stringable;

abstract class AbstractContent implements ContentInterface
{

    protected string|Stringable|null $filename = null;

    protected string|Stringable|null $mime = null;

    protected string|Stringable|null $charset = "UTF-8";

    protected bool $attachment = false;

    protected string|Stringable|null $etag = null;

    protected DateTimeInterface|null $lastModified = null;

    protected int|null $size = null;

    /**
     * @inheritDoc
     */
    public function filename(): string|Stringable|null
    {
        return $this->filename;
    }

    /**
     * @inheritDoc
     */
    public function mime(): string|Stringable|null
    {
        return $this->mime ?? $this->inferMime($this->filename());
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string|Stringable|null
    {
        $type = $this->mime();
        if ($type === null) {
            return null;
        }
        $text = $type == 'application/json' || str_starts_with($type, 'text/');
        if ($text && $charset = $this->charset()) {
            $type = $type . '; charset=' . $charset;
        }
        return $type;
    }

    /**
     * @inheritDoc
     */
    public function charset(): string|Stringable|null
    {
        return $this->charset;
    }

    /**
     * @inheritDoc
     */
    public function attachment(): bool
    {
        return $this->attachment;
    }

    /**
     * @inheritDoc
     */
    public function etag(): string|Stringable|null
    {
        return $this->etag;
    }

    /**
     * @inheritDoc
     */
    public function lastModified(): DateTimeInterface|null
    {
        return $this->lastModified;
    }

    /**
     * @inheritDoc
     */
    public function setAttachment(bool $attachment): static
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFilename(string|Stringable|null $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function size(): int|null
    {
        return $this->size;
    }

    /**
     * Infer a mime type from a filename.
     * @codeCoverageIgnore this is really just a best-effort mapping, it's silly to test every line here
     */
    protected static function inferMime(string|Stringable|null $filename): string|null
    {
        if ($filename === null) {
            return null;
        }
        $filename = (string) $filename;
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return match ($extension) {
            // Web Core
            'html', 'htm' => 'text/html',
            'css'         => 'text/css',
            'js'          => 'application/javascript',
            'mjs'         => 'application/javascript',
            'json'        => 'application/json',
            'map'         => 'application/json',
            'xml'         => 'application/xml',

            // Images
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'avif'        => 'image/avif',
            'svg', 'svgz' => 'image/svg+xml',
            'ico'         => 'image/x-icon',

            // Audio/Video
            'mp3'         => 'audio/mpeg',
            'wav'         => 'audio/wav',
            'ogg'         => 'audio/ogg',
            'm4a'         => 'audio/mp4',
            'mp4'         => 'video/mp4',
            'webm'        => 'video/webm',
            'ogv'         => 'video/ogg',
            'mov'         => 'video/quicktime',

            // Documents and archives
            'txt'         => 'text/plain',
            'pdf'         => 'application/pdf',
            'zip'         => 'application/zip',
            'gz', 'tgz'   => 'application/x-gzip',
            'rar'         => 'application/x-rar-compressed',
            '7z'          => 'application/x-7z-compressed',
            'csv'         => 'text/csv',
            'rtf'         => 'application/rtf',

            // Fonts
            'woff'        => 'font/woff',
            'woff2'       => 'font/woff2',
            'ttf'         => 'font/ttf',
            'otf'         => 'font/otf',
            'eot'         => 'application/vnd.ms-fontobject',

            // OpenDocument (LibreOffice / OpenOffice)
            'odt'         => 'application/vnd.oasis.opendocument.text',
            'ods'         => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp'         => 'application/vnd.oasis.opendocument.presentation',
            'odg'         => 'application/vnd.oasis.opendocument.graphics',
            'odf'         => 'application/vnd.oasis.opendocument.formula',

            // Microsoft Office (Common)
            'doc', 'dot'  => 'application/msword',
            'docx'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls', 'xlt'  => 'application/vnd.ms-excel',
            'xlsx'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt', 'pps'  => 'application/vnd.ms-powerpoint',
            'pptx'        => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',

            default       => null,
        };
    }

}
