<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Duplicate document number — spec CA-05.1 → 409 DOCUMENT_TAKEN */
final class DocumentTakenException extends DomainException
{
    public function __construct(string $doc)
    {
        parent::__construct("El documento {$doc} ya está registrado.");
    }
}
