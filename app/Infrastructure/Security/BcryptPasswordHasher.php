<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Ports\Outbound\PasswordHasher;
use Illuminate\Support\Facades\Hash;

/** BCrypt password hasher (plan.md D-09 translated to Laravel stack). */
final class BcryptPasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }

    public function verify(string $plainPassword, string $hash): bool
    {
        return Hash::check($plainPassword, $hash);
    }
}
