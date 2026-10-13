<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\ForbiddenRoleException;
use InvalidArgumentException;

/**
 * Entidad User (Usuario del sistema).
 *
 * Invariantes (spec.md HU-01/02 / data-model.md §3):
 * - username se guarda en minúsculas y recortado (CA-02.2).
 * - role solo puede ser 'admin' o 'librarian'.
 * - Pedir role 'admin' por la API lanza ForbiddenRoleException (CA-02.3).
 * - password_hash nunca se devuelve ni se registra en logs (RNF-3).
 */
final class User
{
    private const VALID_ROLES = ['admin', 'librarian'];
    private const API_ALLOWED_ROLES = ['librarian']; // El rol admin solo lo da el entorno

    private string $id;
    private string $username;
    private string $passwordHash;
    private string $role;

    public function __construct(
        string $id,
        string $username,
        string $passwordHash,
        string $role
    ) {
        $this->assertValidRole($role);
        $this->assertNotEmpty($username, 'username');

        $this->id           = $id;
        $this->username     = strtolower(trim($username)); // normalización CA-02.2
        $this->passwordHash = $passwordHash;
        $this->role         = $role;
    }

    /**
     * Crea un User desde una petición de API.
     * Rechaza role='admin' con ForbiddenRoleException (CA-02.3).
     */
    public static function createViaApi(
        string $id,
        string $username,
        string $passwordHash,
        string $role
    ): self {
        if (!in_array($role, self::API_ALLOWED_ROLES, true)) {
            throw new ForbiddenRoleException($role);
        }

        return new self($id, $username, $passwordHash, $role);
    }

    // Getters
    public function id(): string           { return $this->id; }
    public function username(): string     { return $this->username; }
    public function passwordHash(): string { return $this->passwordHash; }
    public function role(): string         { return $this->role; }
    public function isAdmin(): bool        { return $this->role === 'admin'; }

    private function assertValidRole(string $role): void
    {
        if (!in_array($role, self::VALID_ROLES, true)) {
            throw new InvalidArgumentException(
                "Rol inválido: \"{$role}\". Los roles permitidos son: " . implode(', ', self::VALID_ROLES) . '.'
            );
        }
    }

    private function assertNotEmpty(string $value, string $field): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException("El campo {$field} no puede estar vacío.");
        }
    }
}
