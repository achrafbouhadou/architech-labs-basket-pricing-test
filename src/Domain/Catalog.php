<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Domain;

use ArchitechLabs\Basket\Domain\Exception\UnknownProductCode;
use InvalidArgumentException;
use function array_key_exists;
use function array_values;
use function sprintf;
use function strtoupper;
use function trim;

/**
 * Catalog holds all the products and makes lookups easy for the basket.
 */
final class Catalog
{
    /** @var array<string, Product> */
    private array $products;

    /**
     * @param iterable<Product> $products
     */
    /**
     * Load the catalog and guard against duplicate product codes.
     *
     * @param iterable<Product> $products
     */
    public function __construct(iterable $products)
    {
        $normalized = [];
        foreach ($products as $product) {
            $code = $this->normalize($product->code());
            if (array_key_exists($code, $normalized)) {
                throw new InvalidArgumentException(sprintf('Duplicate product code "%s".', $code));
            }

            $normalized[$code] = $product;
        }

        $this->products = $normalized;
    }

    /**
     * Quick way to check if a product exists by code.
     */
    public function has(string $code): bool
    {
        $normalized = $this->normalize($code);

        return array_key_exists($normalized, $this->products);
    }

    /**
     * Fetch a product or throw a domain exception if the code is unknown.
     */
    public function get(string $code): Product
    {
        $normalized = $this->normalize($code);

        if (!array_key_exists($normalized, $this->products)) {
            throw UnknownProductCode::withCode($code);
        }

        return $this->products[$normalized];
    }

    /**
     * @return list<Product>
     */
    /**
     * Return every product as a simple list.
     *
     * @return list<Product>
     */
    public function all(): array
    {
        return array_values($this->products);
    }

    /**
     * Normalise product codes so lookups are case-insensitive and trimmed.
     */
    private function normalize(string $code): string
    {
        $normalized = strtoupper(trim($code));
        if ($normalized === '') {
            throw new InvalidArgumentException('Product code must not be empty.');
        }

        return $normalized;
    }
}
