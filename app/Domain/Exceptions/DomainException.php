<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

/**
 * Excepción base del dominio.
 * Todas las excepciones de negocio extienden esta clase.
 * Solo el adaptador HTTP las traduce al cuerpo de error del contrato.
 */
abstract class DomainException extends RuntimeException {}
