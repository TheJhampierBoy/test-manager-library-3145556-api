<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\ReportReader;
use App\Domain\ValueObjects\DateRange;

/** Use case: most-borrowed report (HU-10 CA-10.1 to CA-10.5). */
final class MostBorrowedReportService
{
    public function __construct(private readonly ReportReader $reader) {}

    public function execute(string $from, string $to): array
    {
        // DateRange throws InvalidArgumentException if to < from → caught by HTTP layer → 422
        $range = DateRange::fromStrings($from, $to);
        return $this->reader->mostBorrowed($range);
    }
}
