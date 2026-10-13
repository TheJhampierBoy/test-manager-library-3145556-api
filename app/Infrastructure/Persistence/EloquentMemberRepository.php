<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Ports\Outbound\MemberRepository;
use App\Domain\Entities\Member;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

final class EloquentMemberRepository implements MemberRepository
{
    private const TABLE = 'library.member';

    public function findById(string $id): ?Member
    {
        $row = DB::table(self::TABLE)->where('id', $id)->first();
        return $row ? new Member($row->id, $row->full_name, $row->document_number) : null;
    }

    public function save(Member $member): void
    {
        $data = ['id' => $member->id(), 'full_name' => $member->fullName(), 'document_number' => $member->documentNumber()];
        $exists = DB::table(self::TABLE)->where('id', $member->id())->exists();
        $exists ? DB::table(self::TABLE)->where('id', $member->id())->update($data) : DB::table(self::TABLE)->insert($data);
    }

    public function existsByDocument(string $documentNumber): bool
    {
        return DB::table(self::TABLE)->where('document_number', $documentNumber)->exists();
    }

    public function search(?string $q, int $page, int $size): array
    {
        $query = DB::table(self::TABLE);
        if ($q) {
            $like = '%' . $q . '%';
            $query->where(fn($s) => $s->where('full_name', 'ilike', $like)->orWhere('document_number', 'ilike', $like));
        }
        $total = (clone $query)->count();
        $rows  = $query->orderBy('full_name')->offset(($page - 1) * $size)->limit($size)->get();
        return ['items' => $rows->map(fn($r) => new Member($r->id, $r->full_name, $r->document_number))->all(), 'total' => $total];
    }

    public function countUnreturned(string $memberId): int
    {
        return (int) DB::table('library.loan_item as li')
            ->join('library.loan as l', 'l.id', '=', 'li.loan_id')
            ->where('l.member_id', $memberId)
            ->whereNull('li.returned_at')
            ->count();
    }

    public function hasOverdue(string $memberId): bool
    {
        return DB::table('library.loan_item as li')
            ->join('library.loan as l', 'l.id', '=', 'li.loan_id')
            ->where('l.member_id', $memberId)
            ->whereNull('li.returned_at')
            ->where('li.due_at', '<', now())
            ->exists();
    }
}
