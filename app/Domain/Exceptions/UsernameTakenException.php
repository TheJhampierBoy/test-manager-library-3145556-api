<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Username ya existe — spec.md CA-02.2 → 409 USERNAME_TAKEN */
final class UsernameTakenException extends DomainException
{
    public function __construct(string $username)
    {
        parent::__construct("El nombre de usuario \"{$username}\" ya está en uso.");
    }
}
