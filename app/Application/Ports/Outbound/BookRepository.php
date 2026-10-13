<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\Book;

interface BookRepository
{
    public function findById(string $id): ?Book;

    /** @return Book[] */
    public function findByIds(array $ids): array;

    public function save(Book $book): void;

    public function existsByIsbnActive(string $isbn, ?string $excludeId = null): bool;

    public function hasOpenLoans(string $bookId): bool;

    /**
     * @return array{items: Book[], total: int}
     */
    public function search(
        ?string $q,
        ?string $categoryId,
        int $page,
        int $size
    ): array;
}
