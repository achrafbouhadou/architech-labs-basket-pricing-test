<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Tests\Pricing;

use ArchitechLabs\Basket\Domain\Money;
use ArchitechLabs\Basket\Pricing\DeliveryRules;
use ArchitechLabs\Basket\Pricing\DeliveryTier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeliveryRulesTest extends TestCase
{
    #[DataProvider('feeExamples')]
    public function testFeeForSubtotal(int $subtotalAmount, int $expectedFee): void
    {
        $rules = new DeliveryRules([
            new DeliveryTier(Money::of(5000, 'USD'), Money::of(495, 'USD')),
            new DeliveryTier(Money::of(9000, 'USD'), Money::of(295, 'USD')),
            new DeliveryTier(null, Money::of(0, 'USD')),
        ]);

        $subtotal = Money::of($subtotalAmount, 'USD');

        self::assertSame($expectedFee, $rules->feeFor($subtotal)->amount());
    }

    public static function feeExamples(): array
    {
        return [
            'below first threshold' => [3785, 495],
            'just below second threshold' => [8999, 295],
            'exactly second threshold' => [9000, 0],
            'well above thresholds' => [12000, 0],
        ];
    }
}
