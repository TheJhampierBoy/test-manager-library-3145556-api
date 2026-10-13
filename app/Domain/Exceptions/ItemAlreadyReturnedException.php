<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Línea ya devuelta — spec.md CA-07.2 → 409 ITEM_ALREADY_RETURNED */
final class ItemAlreadyReturnedException extends DomainException
{
    public function __construct(string $itemId)
    {
        parent::__construct("La línea de préstamo {$itemId} ya fue devuelta.");
    }
}
