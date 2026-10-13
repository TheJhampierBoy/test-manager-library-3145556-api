<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\DuplicateBookInLoanException;
use App\Domain\Exceptions\ItemAlreadyReturnedException;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Agregado Loan (Préstamo).
 *
 * Invariantes (spec.md §3 HU-06/07/08):
 * - 1 a 3 líneas por préstamo (CA-06.1).
 * - Sin libro repetido (CA-06.2) — AddItem valida.
 * - due_at = loaned_at + 14 días (CA-06.6).
 * - Estado calculado: open | overdue | closed (CA-08.1 / Artículo VII).
 * - Inmutable salvo returned_at de cada línea (CA-06.7).
 */
final class Loan
{
    public const LOAN_DAYS    = 14;
    public const MAX_ITEMS    = 3;

    private string            $id;
    private string            $memberId;
    private string            $loanedByUserId;
    private DateTimeImmutable $loanedAt;

    /** @var LoanItem[] */
    private array $items;

    /**
     * @param LoanItem[] $items
     */
    public function __construct(
        string            $id,
        string            $memberId,
        string            $loanedByUserId,
        DateTimeImmutable $loanedAt,
        array             $items = []
    ) {
        $this->id             = $id;
        $this->memberId       = $memberId;
        $this->loanedByUserId = $loanedByUserId;
        $this->loanedAt       = $loanedAt;
        $this->items          = $items;
    }

    // ------------------------------------------------------------------
    // Comportamiento
    // ------------------------------------------------------------------

    /**
     * Añade una línea al préstamo.
     * - Llama a Book::withdraw() para descontar el ejemplar.
     * - Verifica que no supere MAX_ITEMS (validado antes en el servicio).
     * - Verifica que no haya libro repetido.
     *
     * El título, ISBN y categoría se congelan aquí (RN-4).
     */
    public function addItem(
        string            $itemId,
        Book              $book,
        string            $categoryName,
        DateTimeImmutable $now
    ): LoanItem {
        // Sin libro repetido
        foreach ($this->items as $existing) {
            if ($existing->bookId() === $book->id()) {
                throw new DuplicateBookInLoanException($book->id());
            }
        }

        if (count($this->items) >= self::MAX_ITEMS) {
            throw new InvalidArgumentException('Un préstamo no puede tener más de ' . self::MAX_ITEMS . ' libros.');
        }

        // Descuenta el ejemplar del Book (invariante de Book)
        $book->withdraw();

        $dueAt = $now->modify('+' . self::LOAN_DAYS . ' days');

        $item = new LoanItem(
            id:           $itemId,
            loanId:       $this->id,
            bookId:       $book->id(),
            bookTitle:    $book->title(),     // congelado
            isbn:         (string) $book->isbn(), // congelado
            categoryName: $categoryName,       // congelado
            dueAt:        $dueAt,
            returnedAt:   null
        );

        $this->items[] = $item;

        return $item;
    }

    /**
     * Devuelve una línea del préstamo.
     * - Llama a Book::restock() para sumar el ejemplar.
     * - Lanza ItemAlreadyReturnedException si ya estaba devuelta.
     */
    public function returnItem(string $itemId, Book $book, DateTimeImmutable $now): LoanItem
    {
        $item = $this->findItem($itemId);
        $item->returnItem($now);
        $book->restock();
        return $item;
    }

    /**
     * Estado calculado del préstamo (Artículo VII / CA-08.1).
     * No se almacena: se calcula en tiempo de ejecución.
     */
    public function status(DateTimeImmutable $now): string
    {
        $unreturned = array_filter($this->items, fn(LoanItem $i) => !$i->isReturned());

        if (empty($unreturned)) {
            return 'closed';
        }

        foreach ($unreturned as $item) {
            if ($item->isOverdue($now)) {
                return 'overdue';
            }
        }

        return 'open';
    }

    // ------------------------------------------------------------------
    // Getters
    // ------------------------------------------------------------------

    public function id(): string                 { return $this->id; }
    public function memberId(): string           { return $this->memberId; }
    public function loanedByUserId(): string     { return $this->loanedByUserId; }
    public function loanedAt(): DateTimeImmutable { return $this->loanedAt; }

    /** @return LoanItem[] */
    public function items(): array { return $this->items; }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function findItem(string $itemId): LoanItem
    {
        foreach ($this->items as $item) {
            if ($item->id() === $itemId) {
                return $item;
            }
        }

        throw new \DomainException("Línea de préstamo {$itemId} no encontrada en el préstamo {$this->id}.");
    }
}
