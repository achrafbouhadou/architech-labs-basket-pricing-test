<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Tests\Basket;

use ArchitechLabs\Basket\Basket;
use ArchitechLabs\Basket\Domain\Catalog;
use ArchitechLabs\Basket\Domain\Exception\UnknownProductCode;
use ArchitechLabs\Basket\Domain\Money;
use ArchitechLabs\Basket\Domain\Product;
use ArchitechLabs\Basket\Pricing\DeliveryRules;
use ArchitechLabs\Basket\Pricing\DeliveryTier;
use ArchitechLabs\Basket\Pricing\RedSecondHalfPrice;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BasketTest extends TestCase
{
    public function testEmptyBasketReturnsZeroTotals(): void
    {
        $basket = $this->createBasket();

        $totals = $basket->total();

        self::assertSame(0, $totals['total']->amount());
        self::assertSame(0, $totals['subtotal']->amount());
        self::assertSame(0, $totals['discount']->amount());
        self::assertSame(0, $totals['delivery']->amount());
    }

    /**
     * @param list<string> $items
     * @param array{subtotal: int, discount: int, delivery: int, total: int} $expected
     */
    #[DataProvider('basketExamples')]
    public function testBasketTotalsMatchExpected(array $items, array $expected): void
    {
        $basket = $this->createBasket();

        foreach ($items as $code) {
            $basket->add($code);
        }

        $totals = $basket->total();

        self::assertSame($expected['subtotal'], $totals['subtotal']->amount(), 'Subtotal mismatch');
        self::assertSame($expected['discount'], $totals['discount']->amount(), 'Discount mismatch');
        self::assertSame($expected['delivery'], $totals['delivery']->amount(), 'Delivery mismatch');
        self::assertSame($expected['total'], $totals['total']->amount(), 'Total mismatch');
    }

    /**
     * @return array<string, array{0: list<string>, 1: array{subtotal: int, discount: int, delivery: int, total: int}}>
     */
    public static function basketExamples(): array
    {
        return [
            'B01 + G01' => [
                ['B01', 'G01'],
                [
                    'subtotal' => 3290,
                    'discount' => 0,
                    'delivery' => 495,
                    'total' => 3785,
                ],
            ],
            'R01 + R01' => [
                ['R01', 'R01'],
                [
                    'subtotal' => 6590,
                    'discount' => 1648,
                    'delivery' => 495,
                    'total' => 5437,
                ],
            ],
            'R01 + G01' => [
                ['R01', 'G01'],
                [
                    'subtotal' => 5790,
                    'discount' => 0,
                    'delivery' => 295,
                    'total' => 6085,
                ],
            ],
            'B01 + B01 + R01 + R01 + R01' => [
                ['B01', 'B01', 'R01', 'R01', 'R01'],
                [
                    'subtotal' => 11475,
                    'discount' => 1648,
                    'delivery' => 0,
                    'total' => 9827,
                ],
            ],
        ];
    }

    public function testAddingUnknownProductThrows(): void
    {
        $basket = $this->createBasket();

        $this->expectException(UnknownProductCode::class);
        $basket->add('UNKNOWN');
    }

    private function createBasket(): Basket
    {
        $catalog = new Catalog([
            new Product('R01', 'Red Widget', Money::of(3295, 'USD')),
            new Product('G01', 'Green Widget', Money::of(2495, 'USD')),
            new Product('B01', 'Blue Widget', Money::of(795, 'USD')),
        ]);

        $offer = new RedSecondHalfPrice('R01');

        $delivery = new DeliveryRules([
            new DeliveryTier(Money::of(5000, 'USD'), Money::of(495, 'USD')),
            new DeliveryTier(Money::of(9000, 'USD'), Money::of(295, 'USD')),
            new DeliveryTier(null, Money::of(0, 'USD')),
        ]);

        return new Basket($catalog, $offer, $delivery, 'USD');
    }
}
