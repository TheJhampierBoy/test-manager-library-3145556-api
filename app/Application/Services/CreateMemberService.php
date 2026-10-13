<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\MemberRepository;
use App\Domain\Entities\Member;
use App\Domain\Exceptions\DocumentTakenException;
use Ramsey\Uuid\Uuid;

/** Use case: create member (HU-05 CA-05.1). */
final class CreateMemberService
{
    public function __construct(private readonly MemberRepository $members) {}

    public function execute(string $fullName, string $documentNumber): Member
    {
        if ($this->members->existsByDocument(trim($documentNumber))) {
            throw new DocumentTakenException($documentNumber);
        }

        $member = new Member(
            id:             (string) Uuid::uuid4(),
            fullName:       $fullName,
            documentNumber: $documentNumber,
        );

        $this->members->save($member);
        return $member;
    }
}
