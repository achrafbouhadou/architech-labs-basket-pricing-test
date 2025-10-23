<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket;

use ArchitechLabs\Basket\Domain\Catalog;
use ArchitechLabs\Basket\Domain\Money;
use ArchitechLabs\Basket\Pricing\DeliveryRules;
use ArchitechLabs\Basket\Pricing\LineItem;
use ArchitechLabs\Basket\Pricing\LineItems;
use ArchitechLabs\Basket\Pricing\Offer;
use InvalidArgumentException;
use function strtoupper;

/**
 * Basket ties the catalog, offers, and delivery rules together.
 */
final class Basket
{
    private Catalog $catalog;
    private Offer $offer;
    private DeliveryRules $deliveryRules;
    private string $currency;
    /** @var array<string, LineItem> */
    private array $lineItems = [];

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
     * Which currency the basket uses for totals.
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Add a product to the basket by code.
     */
    public function add(string $code): void
    {
        $product = $this->catalog->get($code);
        $normalizedCode = $product->code();

        if (isset($this->lineItems[$normalizedCode])) {
            $existing = $this->lineItems[$normalizedCode];
            $this->lineItems[$normalizedCode] = new LineItem(
                $existing->product(),
                $existing->quantity() + 1
            );

            return;
        }

        $this->lineItems[$normalizedCode] = new LineItem($product, 1);
    }

    /**
     * Calculate the money breakdown for the basket.
     *
     * @return array{subtotal: Money, discount: Money, delivery: Money, total: Money}
     */
    public function total(): array
    {
        $lineItems = new LineItems($this->lineItems, $this->currency);

        $subtotal = $lineItems->subtotal();
        $discount = $this->offer->computeDiscount($lineItems);
        $discounted = $subtotal->subtract($discount);
        $delivery = $this->deliveryRules->feeFor($discounted);
        $total = $discounted->add($delivery);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery' => $delivery,
            'total' => $total,
        ];
    }
}
