<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\ValueObjects\DateRange;

/** Port: read-only query for the most-borrowed report (HU-10 / ADR-004). */
interface ReportReader
{
    /**
     * Returns rows ordered by times_borrowed desc, then title asc.
     * Uses frozen values from loan_item (RN-4 / CA-10.4).
     * Filters by loan.loaned_at (F-09 resolution).
     *
     * @return array{book_id: string, book_title: string, category_name: string, times_borrowed: int}[]
     */
    public function mostBorrowed(DateRange $range): array;
}
