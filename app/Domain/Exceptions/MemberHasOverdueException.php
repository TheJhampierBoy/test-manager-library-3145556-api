<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Socio con vencidos — spec.md CA-06.5 → 409 MEMBER_HAS_OVERDUE */
final class MemberHasOverdueException extends DomainException
{
    public function __construct()
    {
        parent::__construct('El socio tiene libros vencidos y no puede pedir otro préstamo.');
    }
}
