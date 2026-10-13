<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\BookRepository;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Domain\Exceptions\IsbnTakenException;
use App\Domain\ValueObjects\Quantity;
use InvalidArgumentException;

/** Use case: update book title, author, category, total (HU-03 CA-03.3). */
final class UpdateBookService
{
    public function __construct(
        private readonly BookRepository     $books,
        private readonly CategoryRepository $categories,
    ) {}

    public function execute(string $id, string $title, string $author, string $categoryId, int $totalCopies): \App\Domain\Entities\Book
    {
        $book = $this->books->findById($id);
        if ($book === null || !$book->isActive()) {
            throw new \App\Domain\Exceptions\NotFoundException("Libro {$id} no encontrado.");
        }

        $book->updateTotal(new Quantity($totalCopies));

        // Rebuild with new title/author/category (immutable entity — create updated version)
        $updated = new \App\Domain\Entities\Book(
            id:              $book->id(),
            title:           $title,
            author:          $author,
            isbn:            $book->isbn(),
            categoryId:      $categoryId,
            totalCopies:     new Quantity($book->totalCopies()),
            availableCopies: $book->availableCopies(),
            coverKey:        $book->coverKey(),
            deletedAt:       $book->deletedAt(),
            version:         $book->version(),
        );

        $this->books->save($updated);
        return $updated;
    }
}
