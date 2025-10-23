<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Pricing;

use ArchitechLabs\Basket\Domain\Money;
use ArchitechLabs\Basket\Domain\Product;
use InvalidArgumentException;

/**
 * Small immutable record that tracks a product and how many units are in the basket.
 */
final class LineItem
{
    private Product $product;
    private int $quantity;

    public function __construct(Product $product, int $quantity)
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Line item quantity must be greater than zero.');
        }

        $this->product = $product;
        $this->quantity = $quantity;
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    /**
     * Handy alias so offers can grab the unit price quickly.
     */
    public function unitPrice(): Money
    {
        return $this->product->price();
    }

    /**
     * Subtotal for this line item (unit price multiplied by quantity).
     */
    public function subtotal(): Money
    {
        return $this->product->price()->multiply($this->quantity);
    }
}
