<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\BookRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\LoanRepository;
use App\Domain\Entities\LoanItem;
use App\Domain\Exceptions\NotFoundException;

/** Use case: return one loan line (HU-07). */
final class ReturnItemService
{
    public function __construct(
        private readonly LoanRepository $loans,
        private readonly BookRepository $books,
        private readonly Clock          $clock,
    ) {}

    public function execute(string $loanId, string $itemId): LoanItem
    {
        $loan = $this->loans->findById($loanId);
        if ($loan === null) {
            throw new NotFoundException("Préstamo {$loanId} no encontrado.");
        }

        $book = null;
        foreach ($loan->items() as $item) {
            if ($item->id() === $itemId) {
                $book = $this->books->findById($item->bookId());
                break;
            }
        }

        if ($book === null) {
            throw new NotFoundException("Línea {$itemId} no encontrada.");
        }

        // returnItem throws ItemAlreadyReturnedException if already returned (CA-07.2)
        $returned = $loan->returnItem($itemId, $book, $this->clock->now());

        $this->loans->saveItem($returned);
        $this->books->save($book);

        return $returned;
    }
}
