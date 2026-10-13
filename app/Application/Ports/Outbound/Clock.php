<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

use DateTimeImmutable;

/** Port: clock. Allows injecting a fake clock in tests (plan.md D-05). */
interface Clock
{
    public function now(): DateTimeImmutable;
}
