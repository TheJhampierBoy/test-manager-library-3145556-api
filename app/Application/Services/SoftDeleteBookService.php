<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\BookRepository;
use App\Application\Ports\Outbound\CoverStorage;
use App\Domain\Exceptions\NotFoundException;
use DateTimeImmutable;

/** Use case: soft-delete book (HU-03 CA-03.5). */
final class SoftDeleteBookService
{
    public function __construct(
        private readonly BookRepository $books,
        private readonly CoverStorage   $covers,
    ) {}

    public function execute(string $id): void
    {
        $book = $this->books->findById($id);
        if ($book === null || !$book->isActive()) {
            throw new NotFoundException("Libro {$id} no encontrado.");
        }

        $hasOpenLoans = $this->books->hasOpenLoans($id);
        $book->softDelete($hasOpenLoans, new DateTimeImmutable('now', new \DateTimeZone('UTC')));

        // If there's a cover, null it first then delete binary (data-model.md §7)
        $coverKey = $book->coverKey();
        $book->removeCoverKey();
        $this->books->save($book);

        if ($coverKey !== null) {
            $this->covers->delete($coverKey);
        }
    }
}
