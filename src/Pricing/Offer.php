<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Pricing;

use ArchitechLabs\Basket\Domain\Money;

/**
 * Every offer returns how much it should discount from the basket.
 */
interface Offer
{
    /**
     * Work out the discount based on the current basket line items.
     */
    public function computeDiscount(LineItems $lineItems): Money;
}
