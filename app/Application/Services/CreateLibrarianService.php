<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UsernameTakenException;
use Ramsey\Uuid\Uuid;

/**
 * Caso de uso: Dar de alta bibliotecarios (HU-02).
 *
 * CA-02.1 — Admin crea usuario con nombre, clave y role librarian.
 * CA-02.2 — Username guardado en minúsculas y recortado; único → 409.
 * CA-02.3 — Role admin por API → 422 (lo lanza User::createViaApi()).
 * RNF-3   — La clave nunca se devuelve ni se registra en logs.
 */
final class CreateLibrarianService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher,
    ) {}

    /**
     * @return array{id: string, username: string, role: string}
     * @throws UsernameTakenException
     * @throws \App\Domain\Exceptions\ForbiddenRoleException
     */
    public function execute(string $username, string $plainPassword, string $role): array
    {
        $normalized = strtolower(trim($username));

        if ($this->users->existsByUsername($normalized)) {
            throw new UsernameTakenException($normalized);
        }

        $hash = $this->hasher->hash($plainPassword);

        $user = User::createViaApi(
            id:           (string) Uuid::uuid4(),
            username:     $normalized,
            passwordHash: $hash,
            role:         $role,
        );

        $this->users->save($user);

        // Nunca devolvemos password_hash (RNF-3 / api-contract.md §3.2)
        return [
            'id'       => $user->id(),
            'username' => $user->username(),
            'role'     => $user->role(),
        ];
    }
}
