<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\Member;

interface MemberRepository
{
    public function findById(string $id): ?Member;
    public function save(Member $member): void;
    public function existsByDocument(string $documentNumber): bool;
    public function search(?string $q, int $page, int $size): array;
    public function countUnreturned(string $memberId): int;
    public function hasOverdue(string $memberId): bool;
}
