<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Sin ejemplares disponibles — spec.md CA-06.3 → 409 BOOK_NOT_AVAILABLE */
final class BookNotAvailableException extends DomainException
{
    public function __construct(string $bookTitle)
    {
        parent::__construct("No quedan ejemplares disponibles de «{$bookTitle}».");
    }
}
