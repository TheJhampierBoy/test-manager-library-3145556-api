<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

/**
 * Port: binary cover storage (disk locally, S3 in production).
 * The database only stores the opaque cover_key, never the path or bytes.
 */
interface CoverStorage
{
    /** Stores the binary and returns an opaque key. */
    public function store(string $bookId, string $contents, string $mimeType): string;

    /** Returns the binary contents or null if not found. */
    public function get(string $coverKey): ?string;

    /** Returns the MIME type for the stored cover. */
    public function mimeType(string $coverKey): ?string;

    /** Deletes the binary. Must be called after nulling cover_key in the DB. */
    public function delete(string $coverKey): void;
}
