<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Duplicate ISBN among active books — spec CA-03.2 → 409 ISBN_TAKEN */
final class IsbnTakenException extends DomainException
{
    public function __construct(string $isbn)
    {
        parent::__construct("El ISBN {$isbn} ya pertenece a un libro activo.");
    }
}
