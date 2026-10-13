<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Objeto de valor Quantity.
 *
 * Reglas (spec.md CA-03.1 / RN-1):
 * - Entero estrictamente mayor que 0.
 * - Inmutable.
 */
final class Quantity
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException(
                "La cantidad debe ser mayor que 0. Recibido: {$value}."
            );
        }

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
