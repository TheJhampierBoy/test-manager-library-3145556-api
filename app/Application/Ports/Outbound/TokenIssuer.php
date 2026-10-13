<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use App\Domain\Entities\User;

/**
 * Puerto TokenIssuer.
 * Genera un JWT firmado con sub y role. Sin datos personales (api-contract.md §4).
 */
interface TokenIssuer
{
    /**
     * Emite un token para el usuario dado.
     *
     * @return array{access_token: string, token_type: string, expires_in: int, role: string}
     */
    public function issue(User $user): array;
}
