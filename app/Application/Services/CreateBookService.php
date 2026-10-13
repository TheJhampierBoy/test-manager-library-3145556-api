<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\BookRepository;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Domain\Entities\Book;
use App\Domain\Exceptions\IsbnTakenException;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use Ramsey\Uuid\Uuid;

/**
 * Use case: Create book (HU-03 CA-03.1 / CA-03.2).
 * available_copies starts equal to total_copies (CA-03.1).
 */
final class CreateBookService
{
    public function __construct(
        private readonly BookRepository     $books,
        private readonly CategoryRepository $categories,
    ) {}

    public function execute(
        string $title,
        string $author,
        string $isbn,
        string $categoryId,
        int    $totalCopies
    ): Book {
        $isbnVo = new Isbn($isbn);

        if ($this->books->existsByIsbnActive($isbnVo->value())) {
            throw new IsbnTakenException($isbnVo->value());
        }

        $qty  = new Quantity($totalCopies);
        $book = new Book(
            id:              (string) Uuid::uuid4(),
            title:           $title,
            author:          $author,
            isbn:            $isbnVo,
            categoryId:      $categoryId,
            totalCopies:     $qty,
            availableCopies: $qty->value(), // starts equal to total (CA-03.1)
            coverKey:        null,
            deletedAt:       null,
            version:         0,
        );

        $this->books->save($book);

        return $book;
    }
}
