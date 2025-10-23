<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Domain;

use InvalidArgumentException;
use function strtoupper;
use function trim;

/**
 * Simple product entity so the basket can keep code, name, and price together.
 */
final class Product
{
    private string $code;
    private string $name;
    private Money $price;

    /**
     * Constructor trims inputs and keeps codes uppercase for consistency.
     */
    public function __construct(string $code, string $name, Money $price)
    {
        $normalizedCode = strtoupper(trim($code));

        if ($normalizedCode === '') {
            throw new InvalidArgumentException('Product code must not be empty.');
        }

        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Product name must not be empty.');
        }

        $this->code = $normalizedCode;
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * Product code like R01 or G01.
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * Human friendly product name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Money value object for the product price.
     */
    public function price(): Money
    {
        return $this->price;
    }
}
