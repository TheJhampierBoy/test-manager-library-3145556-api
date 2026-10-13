<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

interface CategoryRepository
{
    /** @return array{id: string, name: string}[] */
    public function all(): array;

    public function findById(string $id): ?array;
}
