<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

/**
 * Puerto PasswordHasher.
 * El dominio y la aplicación nunca ven la clave en claro después del hash (RNF-3 / Art. IX).
 */
interface PasswordHasher
{
    /** Genera el hash de la clave en texto claro. */
    public function hash(string $plainPassword): string;

    /** Verifica que la clave en claro coincide con el hash almacenado. */
    public function verify(string $plainPassword, string $hash): bool;
}
