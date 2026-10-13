<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;

interface LoanRepository
{
    public function findById(string $id): ?Loan;
    public function findItemById(string $loanId, string $itemId): ?LoanItem;
    public function save(Loan $loan): void;
    public function saveItem(LoanItem $item): void;
    public function list(?string $from, ?string $to, ?string $status, ?string $memberId, int $page, int $size): array;
    public function overdueItems(): array;
}
