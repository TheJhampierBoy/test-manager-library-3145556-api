<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\ItemAlreadyReturnedException;
use DateTimeImmutable;

/**
 * Línea de préstamo.
 *
 * - Guarda título, ISBN y categoría congelados en el instante del préstamo (RN-4).
 * - due_at = loaned_at + 14 días, calculado por el dominio (CA-06.6).
 * - returned_at es null mientras no se devuelve.
 * - Devolver dos veces lanza ItemAlreadyReturnedException (CA-07.2).
 */
final class LoanItem
{
    private string             $id;
    private string             $loanId;
    private string             $bookId;
    private string             $bookTitle;     // congelado
    private string             $isbn;          // congelado
    private string             $categoryName;  // congelado
    private DateTimeImmutable  $dueAt;
    private ?DateTimeImmutable $returnedAt;

    public function __construct(
        string             $id,
        string             $loanId,
        string             $bookId,
        string             $bookTitle,
        string             $isbn,
        string             $categoryName,
        DateTimeImmutable  $dueAt,
        ?DateTimeImmutable $returnedAt
    ) {
        $this->id           = $id;
        $this->loanId       = $loanId;
        $this->bookId       = $bookId;
        $this->bookTitle    = $bookTitle;
        $this->isbn         = $isbn;
        $this->categoryName = $categoryName;
        $this->dueAt        = $dueAt;
        $this->returnedAt   = $returnedAt;
    }

    /** Devuelve la línea. Lanza ItemAlreadyReturnedException si ya estaba devuelta. */
    public function returnItem(DateTimeImmutable $now): void
    {
        if ($this->returnedAt !== null) {
            throw new ItemAlreadyReturnedException($this->id);
        }

        $this->returnedAt = $now;
    }

    public function isReturned(): bool
    {
        return $this->returnedAt !== null;
    }

    public function isOverdue(DateTimeImmutable $now): bool
    {
        return $this->returnedAt === null && $now > $this->dueAt;
    }

    // Getters
    public function id(): string                       { return $this->id; }
    public function loanId(): string                   { return $this->loanId; }
    public function bookId(): string                   { return $this->bookId; }
    public function bookTitle(): string                { return $this->bookTitle; }
    public function isbn(): string                     { return $this->isbn; }
    public function categoryName(): string             { return $this->categoryName; }
    public function dueAt(): DateTimeImmutable         { return $this->dueAt; }
    public function returnedAt(): ?DateTimeImmutable   { return $this->returnedAt; }
}
