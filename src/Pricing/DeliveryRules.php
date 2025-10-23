<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Pricing;

use ArchitechLabs\Basket\Domain\Money;
use InvalidArgumentException;
use function array_values;
use function count;
use function usort;

/**
 * Applies tiered delivery pricing once the basket subtotal is known.
 */
final class DeliveryRules
{
    /** @var list<DeliveryTier> */
    private array $tiers;

    /**
     * @param iterable<DeliveryTier> $tiers
     */
    public function __construct(iterable $tiers)
    {
        $this->tiers = [];
        foreach ($tiers as $tier) {
            $this->tiers[] = $tier;
        }

        if ($this->tiers === []) {
            throw new InvalidArgumentException('Delivery rules need at least one tier.');
        }

        $this->sortTiers();
        $this->assertSingleCurrency();
    }

    /**
     * Return the right delivery fee for the given subtotal.
     */
    public function feeFor(Money $subtotal): Money
    {
        foreach ($this->tiers as $tier) {
            $upperBound = $tier->upperBound();

            if ($upperBound === null) {
                return $tier->fee();
            }

            if ($subtotal->compare($upperBound) < 0) {
                return $tier->fee();
            }
        }

        return $this->tiers[count($this->tiers) - 1]->fee();
    }

    private function sortTiers(): void
    {
        usort(
            $this->tiers,
            static function (DeliveryTier $a, DeliveryTier $b): int {
                $upperA = $a->upperBound();
                $upperB = $b->upperBound();

                if ($upperA === null) {
                    return 1;
                }

                if ($upperB === null) {
                    return -1;
                }

                return $upperA->compare($upperB);
            }
        );

        $this->tiers = array_values($this->tiers);
    }

    private function assertSingleCurrency(): void
    {
        $currency = $this->tiers[0]->fee()->currency();

        foreach ($this->tiers as $tier) {
            if ($tier->fee()->currency() !== $currency) {
                throw new InvalidArgumentException('All delivery fees must share the same currency.');
            }

            $upperBound = $tier->upperBound();
            if ($upperBound !== null && $upperBound->currency() !== $currency) {
                throw new InvalidArgumentException('Tier upper bounds must use the same currency as the fees.');
            }
        }
    }
}
