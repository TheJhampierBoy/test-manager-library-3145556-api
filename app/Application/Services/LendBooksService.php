<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\BookRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\LoanRepository;
use App\Application\Ports\Outbound\MemberRepository;
use App\Domain\Entities\Loan;
use App\Domain\Exceptions\MemberHasOverdueException;
use App\Domain\Exceptions\MemberLimitExceededException;
use App\Domain\Exceptions\NotFoundException;
use Ramsey\Uuid\Uuid;

/**
 * Use case: lend books (HU-06).
 * CA-06.1 to CA-06.7 enforced here and in domain aggregates.
 */
final class LendBooksService
{
    private const MAX_UNRETURNED = 3;

    public function __construct(
        private readonly MemberRepository $members,
        private readonly BookRepository   $books,
        private readonly LoanRepository   $loans,
        private readonly Clock            $clock,
    ) {}

    /**
     * @param array<string> $bookIds 1-3 book UUIDs
     */
    public function execute(string $memberId, string $loanedByUserId, array $bookIds): Loan
    {
        $now    = $this->clock->now();
        $member = $this->members->findById($memberId);
        if ($member === null) {
            throw new NotFoundException("Socio {$memberId} no encontrado.");
        }

        // CA-06.5 — member with overdue cannot borrow
        if ($this->members->hasOverdue($memberId)) {
            throw new MemberHasOverdueException();
        }

        // CA-06.4 — max 3 unreturned total
        $currentUnreturned = $this->members->countUnreturned($memberId);
        $requested         = count($bookIds);
        if ($currentUnreturned + $requested > self::MAX_UNRETURNED) {
            throw new MemberLimitExceededException($currentUnreturned);
        }

        // Load books by batch (Q3)
        $books = $this->books->findByIds($bookIds);
        if (count($books) !== count($bookIds)) {
            throw new NotFoundException('Uno o más libros no existen o están dados de baja.');
        }

        $loan = new Loan(
            id:             (string) Uuid::uuid4(),
            memberId:       $memberId,
            loanedByUserId: $loanedByUserId,
            loanedAt:       $now,
        );

        // Build an id→book map for lookup
        $bookMap = [];
        foreach ($books as $b) {
            $bookMap[$b->id()] = $b;
        }

        foreach ($bookIds as $bookId) {
            $book         = $bookMap[$bookId];
            $categoryName = ''; // resolved by persistence adapter from category table
            // We pass an empty string here; the persistence layer resolves category name
            // via the book's category_id when building the frozen loan_item row.
            $loan->addItem(
                itemId:       (string) Uuid::uuid4(),
                book:         $book,
                categoryName: $categoryName,
                now:          $now,
            );
        }

        $this->loans->save($loan);

        // Persist updated available_copies for each book
        foreach ($books as $b) {
            $this->books->save($b);
        }

        return $loan;
    }
}
