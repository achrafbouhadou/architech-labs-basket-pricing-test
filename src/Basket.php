<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket;

use ArchitechLabs\Basket\Domain\Catalog;
use ArchitechLabs\Basket\Pricing\DeliveryRules;
use ArchitechLabs\Basket\Pricing\Offer;
use InvalidArgumentException;
use function strtoupper;

/**
 * Basket orchestrates catalog lookups, offers, and delivery pricing.
 *
 */
final class Basket
{
    private Catalog $catalog;
    private Offer $offer;
    private DeliveryRules $deliveryRules;
    private string $currency;

    /**
     * Currency is stored in uppercase to stay consistent with the money value object.
     */

    public function __construct(Catalog $catalog, Offer $offer, DeliveryRules $deliveryRules, string $currency)
    {
        $currency = strtoupper($currency);
        if ($currency === '') {
            throw new InvalidArgumentException('Basket currency must be provided.');
        }

        $this->catalog = $catalog;
        $this->offer = $offer;
        $this->deliveryRules = $deliveryRules;
        $this->currency = $currency;
    }

    /**
     * Simple accessor so other services can check which currency the basket uses.
     */
    public function currency(): string
    {
        return $this->currency;
    }
}
