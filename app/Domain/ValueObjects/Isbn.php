<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Objeto de valor ISBN-13.
 *
 * Reglas (data-model.md §3 / spec.md CA-03.2):
 * - Exactamente 13 dígitos ASCII.
 * - Solo dígitos: ningún carácter no numérico.
 * - Inmutable.
 */
final class Isbn
{
    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (!preg_match('/^\d{13}$/', $trimmed)) {
            throw new InvalidArgumentException(
                "El ISBN debe tener exactamente 13 dígitos. Recibido: \"{$value}\"."
            );
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
