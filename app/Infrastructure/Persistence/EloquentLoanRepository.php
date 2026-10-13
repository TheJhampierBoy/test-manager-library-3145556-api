<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Ports\Outbound\LoanRepository;
use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

final class EloquentLoanRepository implements LoanRepository
{
    public function findById(string $id): ?Loan
    {
        $row = DB::table('library.loan')->where('id', $id)->first();
        if (!$row) return null;

        $items = DB::table('library.loan_item')->where('loan_id', $id)->get();
        $loanItems = $items->map(fn($i) => $this->hydrateItem($i))->all();

        return new Loan(
            id:             $row->id,
            memberId:       $row->member_id,
            loanedByUserId: $row->loaned_by_user_id,
            loanedAt:       new DateTimeImmutable($row->loaned_at, new DateTimeZone('UTC')),
            items:          $loanItems,
        );
    }

    public function findItemById(string $loanId, string $itemId): ?LoanItem
    {
        $row = DB::table('library.loan_item')->where('id', $itemId)->where('loan_id', $loanId)->first();
        return $row ? $this->hydrateItem($row) : null;
    }

    public function save(Loan $loan): void
    {
        $loanData = [
            'id'                 => $loan->id(),
            'member_id'          => $loan->memberId(),
            'loaned_by_user_id'  => $loan->loanedByUserId(),
            'loaned_at'          => $loan->loanedAt()->format('Y-m-d H:i:sP'),
        ];

        $exists = DB::table('library.loan')->where('id', $loan->id())->exists();
        $exists
            ? DB::table('library.loan')->where('id', $loan->id())->update($loanData)
            : DB::table('library.loan')->insert($loanData);

        foreach ($loan->items() as $item) {
            $this->saveItem($item);
        }
    }

    public function saveItem(LoanItem $item): void
    {
        $data = [
            'id'            => $item->id(),
            'loan_id'       => $item->loanId(),
            'book_id'       => $item->bookId(),
            'book_title'    => $item->bookTitle(),
            'isbn'          => $item->isbn(),
            'category_name' => $item->categoryName(),
            'due_at'        => $item->dueAt()->format('Y-m-d H:i:sP'),
            'returned_at'   => $item->returnedAt()?->format('Y-m-d H:i:sP'),
        ];
        $exists = DB::table('library.loan_item')->where('id', $item->id())->exists();
        $exists
            ? DB::table('library.loan_item')->where('id', $item->id())->update($data)
            : DB::table('library.loan_item')->insert($data);
    }

    public function list(?string $from, ?string $to, ?string $status, ?string $memberId, int $page, int $size): array
    {
        $query = DB::table('library.loan as l')->select('l.*');

        if ($from) $query->where('l.loaned_at', '>=', $from);
        if ($to)   $query->where('l.loaned_at', '<=', $to . ' 23:59:59');
        if ($memberId) $query->where('l.member_id', $memberId);

        $total = (clone $query)->count();
        $rows  = $query->orderByDesc('l.loaned_at')->offset(($page - 1) * $size)->limit($size)->get();

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $loans = [];
        foreach ($rows as $row) {
            $loan = $this->findById($row->id);
            if ($status && $loan->status($now) !== $status) continue;
            $loans[] = $loan;
        }

        return ['items' => $loans, 'total' => $total];
    }

    public function overdueItems(): array
    {
        return DB::table('library.loan_item as li')
            ->join('library.loan as l', 'l.id', '=', 'li.loan_id')
            ->join('library.member as m', 'm.id', '=', 'l.member_id')
            ->whereNull('li.returned_at')
            ->where('li.due_at', '<', now())
            ->orderByRaw('li.due_at ASC')
            ->select([
                'li.id as item_id', 'l.id as loan_id',
                'li.book_title', 'm.full_name as member_name',
                'li.due_at',
                DB::raw("EXTRACT(DAY FROM NOW() - li.due_at)::int AS days_late"),
            ])
            ->get()->toArray();
    }

    private function hydrateItem(object $row): LoanItem
    {
        return new LoanItem(
            id:           $row->id,
            loanId:       $row->loan_id,
            bookId:       $row->book_id,
            bookTitle:    $row->book_title,
            isbn:         $row->isbn,
            categoryName: $row->category_name,
            dueAt:        new DateTimeImmutable($row->due_at, new DateTimeZone('UTC')),
            returnedAt:   $row->returned_at ? new DateTimeImmutable($row->returned_at, new DateTimeZone('UTC')) : null,
        );
    }
}
