<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Pricing;

use ArchitechLabs\Basket\Domain\Money;
use InvalidArgumentException;

/**
 * Single tier in the delivery table. Upper bound is exclusive.
 */
final class DeliveryTier
{
    private ?Money $upperBound;
    private Money $fee;

    public function __construct(?Money $upperBound, Money $fee)
    {
        if ($upperBound !== null && $upperBound->currency() !== $fee->currency()) {
            throw new InvalidArgumentException('Delivery tier must use a single currency.');
        }

        $this->upperBound = $upperBound;
        $this->fee = $fee;
    }

    public function upperBound(): ?Money
    {
        return $this->upperBound;
    }

    public function fee(): Money
    {
        return $this->fee;
    }
}
