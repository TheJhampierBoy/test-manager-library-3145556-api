<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\User;

/**
 * Puerto de repositorio de usuarios.
 * La aplicación lo define; el adaptador de persistencia lo implementa.
 */
interface UserRepository
{
    public function findByUsername(string $username): ?User;

    public function findById(string $id): ?User;

    public function save(User $user): void;

    public function existsByUsername(string $username): bool;
}
