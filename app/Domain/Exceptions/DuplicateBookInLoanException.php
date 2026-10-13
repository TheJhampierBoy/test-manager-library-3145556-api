<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Libro repetido en un préstamo — spec.md CA-06.2 → 422 DUPLICATE_BOOK_IN_LOAN */
final class DuplicateBookInLoanException extends DomainException
{
    public function __construct(string $bookId)
    {
        parent::__construct("El libro {$bookId} aparece más de una vez en el préstamo.");
    }
}
