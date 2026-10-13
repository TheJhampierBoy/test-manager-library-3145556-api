<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\BookHasOpenLoansException;
use App\Domain\Exceptions\BookNotAvailableException;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Agregado Book.
 *
 * Invariantes que defiende (spec.md / data-model.md §4):
 * - available_copies entre 0 y total_copies, siempre (RN-1).
 * - total_copies >= 1 (CA-03.1).
 * - Título no vacío.
 * - Baja lógica: deleted_at marca la baja; un libro dado de baja con préstamos
 *   abiertos lanza BookHasOpenLoansException (CA-03.5).
 * - Withdraw: descuenta 1 disponible; sin disponibles lanza BookNotAvailableException (CA-06.3).
 * - Restock: suma 1 disponible; nunca supera total_copies (la base garantiza el CHECK).
 * - Cambiar total ajusta disponibles en la misma diferencia; no deja disponibles < 0 (CA-03.3).
 */
final class Book
{
    private string            $id;
    private string            $title;
    private string            $author;
    private Isbn              $isbn;
    private string            $categoryId;
    private int               $totalCopies;
    private int               $availableCopies;
    private ?string           $coverKey;
    private ?DateTimeImmutable $deletedAt;
    private int               $version;

    public function __construct(
        string             $id,
        string             $title,
        string             $author,
        Isbn               $isbn,
        string             $categoryId,
        Quantity           $totalCopies,
        int                $availableCopies,
        ?string            $coverKey,
        ?DateTimeImmutable $deletedAt,
        int                $version
    ) {
        $this->assertTitleNotEmpty($title);
        $this->assertAvailableRange($availableCopies, $totalCopies->value());

        $this->id              = $id;
        $this->title           = trim($title);
        $this->author          = $author;
        $this->isbn            = $isbn;
        $this->categoryId      = $categoryId;
        $this->totalCopies     = $totalCopies->value();
        $this->availableCopies = $availableCopies;
        $this->coverKey        = ($coverKey === '' ? null : $coverKey);
        $this->deletedAt       = $deletedAt;
        $this->version         = $version;
    }

    // ------------------------------------------------------------------
    // Comportamiento
    // ------------------------------------------------------------------

    /**
     * Descuenta 1 ejemplar disponible.
     * Lanza BookNotAvailableException si no quedan disponibles.
     */
    public function withdraw(): void
    {
        if ($this->availableCopies < 1) {
            throw new BookNotAvailableException($this->title);
        }

        $this->availableCopies--;
    }

    /**
     * Suma 1 ejemplar disponible (devolución).
     * La base garantiza que no supera total_copies vía CHECK.
     */
    public function restock(): void
    {
        // El CHECK ck_book_available_range actúa como última barrera.
        // Aquí solo incrementamos; la invariante se refuerza en la base.
        $this->availableCopies++;
    }

    /**
     * Baja lógica. Lanza BookHasOpenLoansException si tiene préstamos abiertos.
     *
     * @param bool $hasOpenLoans El repositorio informa si hay préstamos abiertos.
     */
    public function softDelete(bool $hasOpenLoans, DateTimeImmutable $now): void
    {
        if ($hasOpenLoans) {
            throw new BookHasOpenLoansException();
        }

        $this->deletedAt = $now;
    }

    /**
     * Reemplaza total_copies y ajusta available_copies en la misma diferencia.
     * No deja disponibles < 0 (CA-03.3 / 422).
     */
    public function updateTotal(Quantity $newTotal): void
    {
        $diff = $newTotal->value() - $this->totalCopies;
        $newAvailable = $this->availableCopies + $diff;

        if ($newAvailable < 0) {
            throw new InvalidArgumentException(
                "El nuevo total dejaría los disponibles en {$newAvailable}, lo que violaría la regla RN-1."
            );
        }

        $this->assertAvailableRange($newAvailable, $newTotal->value());

        $this->totalCopies     = $newTotal->value();
        $this->availableCopies = $newAvailable;
    }

    /** Actualiza la clave opaca de portada. Cadena vacía se normaliza a null. */
    public function assignCoverKey(?string $key): void
    {
        $this->coverKey = ($key === '' ? null : $key);
    }

    /** Quita la portada. */
    public function removeCoverKey(): void
    {
        $this->coverKey = null;
    }

    // ------------------------------------------------------------------
    // Getters (solo lectura)
    // ------------------------------------------------------------------

    public function id(): string               { return $this->id; }
    public function title(): string            { return $this->title; }
    public function author(): string           { return $this->author; }
    public function isbn(): Isbn               { return $this->isbn; }
    public function categoryId(): string       { return $this->categoryId; }
    public function totalCopies(): int         { return $this->totalCopies; }
    public function availableCopies(): int     { return $this->availableCopies; }
    public function coverKey(): ?string        { return $this->coverKey; }
    public function deletedAt(): ?DateTimeImmutable { return $this->deletedAt; }
    public function isActive(): bool           { return $this->deletedAt === null; }
    public function version(): int             { return $this->version; }

    // ------------------------------------------------------------------
    // Helpers privados
    // ------------------------------------------------------------------

    private function assertTitleNotEmpty(string $title): void
    {
        if (trim($title) === '') {
            throw new InvalidArgumentException('El título del libro no puede estar vacío.');
        }
    }

    private function assertAvailableRange(int $available, int $total): void
    {
        if ($available < 0 || $available > $total) {
            throw new InvalidArgumentException(
                "available_copies ({$available}) debe estar entre 0 y total_copies ({$total})."
            );
        }
    }
}
