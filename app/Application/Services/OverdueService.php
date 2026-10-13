<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\LoanRepository;

/** Use case: list overdue items (HU-09). */
final class OverdueService
{
    public function __construct(private readonly LoanRepository $loans) {}

    public function execute(): array
    {
        return $this->loans->overdueItems();
    }
}
