<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Objeto de valor DateRange.
 *
 * Reglas (spec.md CA-10.2 / api-contract.md §3.7):
 * - `from` y `to` son instantes UTC.
 * - `to` debe ser >= `from`; si no: InvalidArgumentException.
 * - Ambos extremos son inclusivos.
 * - Inmutable.
 */
final class DateRange
{
    private DateTimeImmutable $from;
    private DateTimeImmutable $to;

    public function __construct(DateTimeImmutable $from, DateTimeImmutable $to)
    {
        if ($to < $from) {
            throw new InvalidArgumentException(
                'El fin del rango de fechas no puede ser anterior al inicio.'
            );
        }

        $this->from = $from;
        $this->to   = $to;
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function to(): DateTimeImmutable
    {
        return $this->to;
    }

    /**
     * Crea un DateRange a partir de cadenas de fecha en formato ISO 8601 o YYYY-MM-DD.
     */
    public static function fromStrings(string $from, string $to): self
    {
        $f = DateTimeImmutable::createFromFormat('Y-m-d', $from, new \DateTimeZone('UTC'));
        $t = DateTimeImmutable::createFromFormat('Y-m-d', $to,   new \DateTimeZone('UTC'));

        if ($f === false || $t === false) {
            throw new InvalidArgumentException(
                "Formato de fecha inválido. Se esperaba YYYY-MM-DD. from=\"{$from}\" to=\"{$to}\"."
            );
        }

        // El to es inclusivo: lo llevamos al final del día
        $t = $t->setTime(23, 59, 59);

        return new self($f, $t);
    }

    public function equals(self $other): bool
    {
        return $this->from == $other->from && $this->to == $other->to;
    }
}
