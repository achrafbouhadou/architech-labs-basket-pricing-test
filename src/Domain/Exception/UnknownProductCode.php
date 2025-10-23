<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Domain\Exception;

use DomainException;
use function sprintf;

/**
 * Raised when the basket asks for a product code that does not exist.
 */
final class UnknownProductCode extends DomainException
{
    public static function withCode(string $code): self
    {
        return new self(sprintf('Unknown product code "%s".', $code));
    }
}
