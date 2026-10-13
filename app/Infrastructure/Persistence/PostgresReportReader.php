<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Ports\Outbound\ReportReader;
use App\Domain\ValueObjects\DateRange;
use Illuminate\Support\Facades\DB;

/** Aggregated report query — filters by loan.loaned_at (F-09). Uses frozen values (ADR-004). */
final class PostgresReportReader implements ReportReader
{
    public function mostBorrowed(DateRange $range): array
    {
        return DB::select(<<<SQL
            SELECT
                li.book_id,
                li.book_title,
                li.category_name,
                COUNT(*) AS times_borrowed
            FROM library.loan_item li
            JOIN library.loan l ON l.id = li.loan_id
            WHERE l.loaned_at >= ?
              AND l.loaned_at <= ?
            GROUP BY li.book_id, li.book_title, li.category_name
            ORDER BY times_borrowed DESC, li.book_title ASC
        SQL, [
            $range->from()->format('Y-m-d H:i:sP'),
            $range->to()->format('Y-m-d H:i:sP'),
        ]);
    }
}
