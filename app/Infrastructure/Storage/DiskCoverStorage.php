<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Application\Ports\Outbound\CoverStorage;
use Illuminate\Support\Str;

/**
 * Disk-based cover storage (T-15).
 * Stores files in COVERS_DIR. The key is an opaque UUID-based filename.
 * Never stores path or bytes in the database — only the key.
 */
final class DiskCoverStorage implements CoverStorage
{
    public function __construct(private readonly string $coversDir) {}

    public function store(string $bookId, string $contents, string $mimeType): string
    {
        $ext = $mimeType === 'image/png' ? 'png' : 'jpg';
        $key = Str::uuid()->toString() . '.' . $ext;
        $path = $this->coversDir . DIRECTORY_SEPARATOR . $key;

        if (!is_dir($this->coversDir)) {
            mkdir($this->coversDir, 0775, true);
        }

        file_put_contents($path, $contents);
        return $key;
    }

    public function get(string $coverKey): ?string
    {
        $path = $this->coversDir . DIRECTORY_SEPARATOR . $coverKey;
        return file_exists($path) ? file_get_contents($path) : null;
    }

    public function mimeType(string $coverKey): ?string
    {
        if (str_ends_with($coverKey, '.png')) return 'image/png';
        if (str_ends_with($coverKey, '.jpg')) return 'image/jpeg';
        return null;
    }

    public function delete(string $coverKey): void
    {
        $path = $this->coversDir . DIRECTORY_SEPARATOR . $coverKey;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
