<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Pricing;

use ArchitechLabs\Basket\Domain\Money;
use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use function strtoupper;
use function trim;

/**
 * Read-only collection to help offers inspect the current basket content.
 */
final class LineItems implements IteratorAggregate
{
    /** @var list<LineItem> */
    private array $items;
    private string $currency;

    /**
     * @param iterable<LineItem> $items
     * @param string|null        $currency Optional explicit currency when the basket is empty.
     */
    public function __construct(iterable $items, ?string $currency = null)
    {
        $collected = [];
        $resolvedCurrency = $currency;

        foreach ($items as $item) {
            $collected[] = $item;
            $itemCurrency = $item->unitPrice()->currency();

            if ($resolvedCurrency === null) {
                $resolvedCurrency = $itemCurrency;
                continue;
            }

            if ($itemCurrency !== $resolvedCurrency) {
                throw new InvalidArgumentException('Line items must all share the same currency.');
            }
        }

        if ($resolvedCurrency === null) {
            throw new InvalidArgumentException('Line items require at least one entry or an explicit currency.');
        }

        $this->items = $collected;
        $this->currency = $resolvedCurrency;
    }

    /**
     * Count how many units exist for the given case-insensitive product code.
     */
    public function countByCode(string $code): int
    {
        $normalized = $this->normalizeCode($code);
        $total = 0;

        foreach ($this->items as $item) {
            if ($this->normalizeCode($item->product()->code()) === $normalized) {
                $total += $item->quantity();
            }
        }

        return $total;
    }

    /**
     * Find the first line item for a code so we can read the unit price.
     */
    public function firstForCode(string $code): ?LineItem
    {
        $normalized = $this->normalizeCode($code);

        foreach ($this->items as $item) {
            if ($this->normalizeCode($item->product()->code()) === $normalized) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Calculate the subtotal for the entire basket content.
     */
    public function subtotal(): Money
    {
        $subtotal = Money::zero($this->currency);

        foreach ($this->items as $item) {
            $subtotal = $subtotal->add($item->subtotal());
        }

        return $subtotal;
    }

    /**
     * Currency shared by every line item.
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Gives immutable access to the raw line items.
     *
     * @return list<LineItem>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    private function normalizeCode(string $code): string
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            throw new InvalidArgumentException('Product code must not be empty.');
        }

        return $normalized;
    }
}
