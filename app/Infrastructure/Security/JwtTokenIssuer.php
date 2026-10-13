<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Ports\Outbound\TokenIssuer;
use App\Domain\Entities\User;
use DateTimeImmutable;
use DateTimeZone;

/**
 * JWT token issuer — HS256, 8 hours, sub + role only (api-contract.md §4).
 * Uses raw PHP JWT encoding to avoid external dependency beyond composer.json.
 */
final class JwtTokenIssuer implements TokenIssuer
{
    private const TTL_SECONDS = 8 * 3600; // 8 hours

    public function __construct(private readonly string $secret) {}

    public function issue(User $user): array
    {
        $now     = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $exp     = $now->getTimestamp() + self::TTL_SECONDS;

        $header  = $this->base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode([
            'sub'  => $user->id(),
            'role' => $user->role(),
            'iat'  => $now->getTimestamp(),
            'exp'  => $exp,
        ]));

        $sig = $this->base64url(hash_hmac('sha256', "{$header}.{$payload}", $this->secret, true));

        return [
            'access_token' => "{$header}.{$payload}.{$sig}",
            'token_type'   => 'Bearer',
            'expires_in'   => self::TTL_SECONDS,
            'role'         => $user->role(),
        ];
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
