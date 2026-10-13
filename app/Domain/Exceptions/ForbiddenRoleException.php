<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Rol no permitido por la API — spec.md CA-02.3 → 422 VALIDATION_ERROR */
final class ForbiddenRoleException extends DomainException
{
    public function __construct(string $role)
    {
        parent::__construct("El rol \"{$role}\" no puede asignarse mediante la API.");
    }
}
