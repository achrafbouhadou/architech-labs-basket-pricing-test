<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Domain\Exception;

use DomainException;
use function sprintf;

/**
 * Thrown when two money values with different currencies are combined.
 */
final class CurrencyMismatch extends DomainException
{
    public static function between(string $left, string $right): self
    {
        return new self(sprintf('Currency mismatch: %s vs %s.', $left, $right));
    }
}
