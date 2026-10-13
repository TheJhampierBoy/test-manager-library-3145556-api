<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Socio supera 3 sin devolver — spec.md CA-06.4 → 422 MEMBER_LIMIT_EXCEEDED */
final class MemberLimitExceededException extends DomainException
{
    public function __construct(int $currentCount)
    {
        parent::__construct(
            "El socio ya tiene {$currentCount} libro(s) sin devolver. El límite es 3."
        );
    }
}
