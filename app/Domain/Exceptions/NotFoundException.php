<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/** Resource not found or soft-deleted → 404 NOT_FOUND */
final class NotFoundException extends DomainException {}
