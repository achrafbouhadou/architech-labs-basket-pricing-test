<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Pricing;

use ArchitechLabs\Basket\Domain\Money;

/**
 * Offer implementing “buy one red, get the second half price”.
 */
final class RedSecondHalfPrice implements Offer
{
    private string $targetCode;

    public function __construct(string $targetCode = 'R01')
    {
        $this->targetCode = $targetCode;
    }

    public function computeDiscount(LineItems $lineItems): Money
    {
        $matching = $lineItems->countByCode($this->targetCode);

        if ($matching < 2) {
            return Money::zero($lineItems->currency());
        }

        $item = $lineItems->firstForCode($this->targetCode);
        if ($item === null) {
            return Money::zero($lineItems->currency());
        }

        $pairs = intdiv($matching, 2);
        $halfPrice = $item->unitPrice()->multiplyRatio(1, 2);

        return $halfPrice->multiply($pairs);
    }
}
