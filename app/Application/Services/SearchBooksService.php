<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\BookRepository;

/** Use case: search books (HU-04 CA-04.1 / CA-04.2). */
final class SearchBooksService
{
    private const MAX_SIZE = 50;

    public function __construct(private readonly BookRepository $books) {}

    public function execute(?string $q, ?string $categoryId, int $page, int $size): array
    {
        $size = min($size, self::MAX_SIZE);
        return $this->books->search($q, $categoryId, $page, $size);
    }
}
