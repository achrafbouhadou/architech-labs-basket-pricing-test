<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Tests\Pricing;

use ArchitechLabs\Basket\Domain\Money;
use ArchitechLabs\Basket\Domain\Product;
use ArchitechLabs\Basket\Pricing\LineItem;
use ArchitechLabs\Basket\Pricing\LineItems;
use ArchitechLabs\Basket\Pricing\RedSecondHalfPrice;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RedSecondHalfPriceTest extends TestCase
{
    #[DataProvider('discountExamples')]
    public function testDiscountAppliedPerPair(int $quantity, int $expectedDiscount): void
    {
        $product = new Product('R01', 'Red Widget', Money::of(3295, 'USD'));
        $items = $quantity === 0 ? [] : [new LineItem($product, $quantity)];
        $lineItems = new LineItems($items, 'USD');
        $offer = new RedSecondHalfPrice('R01');

        self::assertSame($expectedDiscount, $offer->computeDiscount($lineItems)->amount());
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function discountExamples(): array
    {
        return [
            'no reds' => [0, 0],
            'single red' => [1, 0],
            'two reds' => [2, 1648],
            'three reds' => [3, 1648],
            'four reds' => [4, 3296],
        ];
    }
}
