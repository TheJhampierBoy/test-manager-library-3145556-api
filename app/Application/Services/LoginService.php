<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Outbound\PasswordHasher;
use App\Application\Ports\Outbound\TokenIssuer;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Exceptions\InvalidCredentialsException;

/**
 * Caso de uso: Iniciar sesión (HU-01).
 *
 * CA-01.1 — Con credenciales válidas devuelve token con 8 h de vigencia.
 * CA-01.2 — Con credenciales inválidas: 401, sin revelar si falló usuario o clave.
 */
final class LoginService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher,
        private readonly TokenIssuer    $tokens,
    ) {}

    /**
     * @return array{access_token: string, token_type: string, expires_in: int, role: string}
     * @throws InvalidCredentialsException
     */
    public function execute(string $username, string $password): array
    {
        $user = $this->users->findByUsername(strtolower(trim($username)));

        // Mismo mensaje tanto si no existe el usuario como si la clave es incorrecta (CA-01.2)
        if ($user === null || !$this->hasher->verify($password, $user->passwordHash())) {
            throw new InvalidCredentialsException();
        }

        return $this->tokens->issue($user);
    }
}
