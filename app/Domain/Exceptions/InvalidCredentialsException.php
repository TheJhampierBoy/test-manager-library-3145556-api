<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Credenciales inválidas — spec.md CA-01.2 → 401 INVALID_CREDENTIALS */
final class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        // El mensaje NO dice si falló el usuario o la clave (CA-01.2)
        parent::__construct('Credenciales inválidas.');
    }
}
