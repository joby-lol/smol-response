<?php

/**
 * smolResponse
 * https://github.com/joby-lol/smol-response
 * (c) 2026 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Response\Content;

use Stringable;

class FileContent extends AbstractRangeContent
{

    /**
     * The number of bytes to read at a time when rendering a range request. More is potentially faster, but uses more RAM.
     * @var positive-int
     */
    public int $render_chunk_size = 8192;

    public readonly string $source_file;

    protected string|null $file_hash = null;

    public function etag(): string|Stringable|null
    {
        if ($this->etag)
            return $this->etag;
        if ($this->file_hash === null) {
            $hash = md5_file($this->source_file);
            if ($hash === false)
                throw new ContentException("Failed to compute file hash for ETag: " . $this->source_file);
            $this->file_hash = $hash;
        }
        return $this->file_hash;
    }

    public function filename(): string|Stringable|null
    {
        return $this->filename
            ?? basename($this->source_file);
    }

    public function size(): int
    {
        $size = filesize($this->source_file);
        if ($size === false)
            throw new ContentException("Failed to get file size for: " . $this->source_file);
        return $size;
    }

    public function __construct(
        string|Stringable $source_file,
        string|Stringable|null $filename = null,
    ) {
        $this->source_file = (string) $source_file;
        if (!file_exists($this->source_file)) {
            throw new ContentException("File does not exist: " . $this->source_file);
        }
        $this->setFilename($filename);
    }

    /**
     * @inheritDoc
     */
    public function renderRange(int|null $start, int|null $end): void
    {
        // validate range
        if (!$this->verifyRange($start, $end)) {
            throw new RangeUnsatisfiableException("Invalid range: {$start}-{$end} for content of size {$this->size()}", $this->size());
        }
        // determine start and end bytes
        $size = $this->size();
        if ($start === null && $end !== null) {
            // "-n" form (the last n bytes)
            $start_byte = max($size - $end, 0);
            $end_byte = $size - 1;
        }
        elseif ($start !== null && $end === null) {
            // "n-" form (from byte n to end)
            $start_byte = $start;
            $end_byte = $size - 1;
        }
        elseif ($start !== null && $end !== null) {
            $start_byte = $start;
            $end_byte = $end;
        }
        else {
            // both null, throw an exception, should never happen due to prior validation
            throw new ContentException("Invalid range: both start and end cannot be null");
        }
        // open file and seek to start byte
        $file = fopen($this->source_file, 'rb');
        if ($file === false) {
            throw new ContentException("Failed to open file for reading: " . $this->source_file);
        }
        if (fseek($file, $start_byte) !== 0) {
            throw new ContentException("Failed to seek to offset $start_byte of file: " . $this->source_file);
        }
        // read and output bytes in chunks until we reach the end byte
        $remaining_bytes = $end_byte - $start_byte + 1;
        while ($remaining_bytes > 0) {
            $bytes_to_read = min($this->render_chunk_size, $remaining_bytes);
            echo fread($file, $bytes_to_read);
            $remaining_bytes -= $bytes_to_read;
        }
        // close file
        fclose($file);
    }

    /**
     * @inheritDoc
     */
    public function render(): void
    {
        // rendering the entire file is easy
        readfile($this->source_file);
    }

}
