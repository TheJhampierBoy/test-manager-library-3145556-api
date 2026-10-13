<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use InvalidArgumentException;

/**
 * Entidad Member (Socio).
 *
 * Invariantes (spec.md HU-05 / data-model.md §3):
 * - full_name y document_number no pueden estar vacíos.
 * - Los socios no se eliminan (CA-05.3): sin deleted_at.
 * - No inicia sesión: no tiene password_hash ni rol.
 */
final class Member
{
    private string $id;
    private string $fullName;
    private string $documentNumber;

    public function __construct(
        string $id,
        string $fullName,
        string $documentNumber
    ) {
        $this->assertNotEmpty($fullName, 'full_name');
        $this->assertNotEmpty($documentNumber, 'document_number');

        $this->id             = $id;
        $this->fullName       = trim($fullName);
        $this->documentNumber = trim($documentNumber);
    }

    // Getters
    public function id(): string             { return $this->id; }
    public function fullName(): string       { return $this->fullName; }
    public function documentNumber(): string { return $this->documentNumber; }

    private function assertNotEmpty(string $value, string $field): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException("El campo {$field} no puede estar vacío.");
        }
    }
}
