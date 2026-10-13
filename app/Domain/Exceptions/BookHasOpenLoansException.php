<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Libro con préstamos abiertos — spec.md CA-03.5 → 409 BOOK_HAS_OPEN_LOANS */
final class BookHasOpenLoansException extends DomainException
{
    public function __construct()
    {
        parent::__construct('El libro tiene préstamos abiertos y no puede darse de baja.');
    }
}
