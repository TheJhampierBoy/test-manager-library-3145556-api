<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\BookRepository;
use App\Application\Ports\Outbound\CoverStorage;
use App\Domain\Exceptions\NotFoundException;
use InvalidArgumentException;

/** Use case: upload/replace cover (HU-03 CA-03.4). Max 2 MB, JPG/PNG only. */
final class UploadCoverService
{
    private const MAX_BYTES = 2 * 1024 * 1024;
    private const ALLOWED   = ['image/jpeg', 'image/png'];

    public function __construct(
        private readonly BookRepository $books,
        private readonly CoverStorage   $covers,
    ) {}

    public function execute(string $bookId, string $contents, string $mimeType): string
    {
        if (!in_array($mimeType, self::ALLOWED, true)) {
            throw new InvalidArgumentException('Solo se admiten imágenes JPG y PNG.');
        }

        if (strlen($contents) > self::MAX_BYTES) {
            throw new InvalidArgumentException('La portada no puede superar 2 MB.');
        }

        $book = $this->books->findById($bookId);
        if ($book === null || !$book->isActive()) {
            throw new NotFoundException("Libro {$bookId} no encontrado.");
        }

        // Delete old cover binary if exists
        $oldKey = $book->coverKey();
        $newKey = $this->covers->store($bookId, $contents, $mimeType);
        $book->assignCoverKey($newKey);
        $this->books->save($book);

        if ($oldKey !== null) {
            $this->covers->delete($oldKey);
        }

        return $newKey;
    }
}
