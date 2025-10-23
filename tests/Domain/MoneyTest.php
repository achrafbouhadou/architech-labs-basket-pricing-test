<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Tests\Domain;

use ArchitechLabs\Basket\Domain\Exception\CurrencyMismatch;
use ArchitechLabs\Basket\Domain\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    #[DataProvider('additionExamples')]
    public function testAddition(Money $left, Money $right, int $expectedAmount): void
    {
        self::assertSame($expectedAmount, $left->add($right)->amount());
    }

    public static function additionExamples(): array
    {
        return [
            'simple sum' => [Money::of(100, 'USD'), Money::of(250, 'USD'), 350],
            'negative numbers' => [Money::of(-400, 'USD'), Money::of(150, 'USD'), -250],
        ];
    }

    #[DataProvider('subtractionExamples')]
    public function testSubtraction(Money $left, Money $right, int $expectedAmount): void
    {
        self::assertSame($expectedAmount, $left->subtract($right)->amount());
    }

    public static function subtractionExamples(): array
    {
        return [
            'basic subtraction' => [Money::of(500, 'USD'), Money::of(200, 'USD'), 300],
            'negative result' => [Money::of(200, 'USD'), Money::of(600, 'USD'), -400],
        ];
    }

    public function testMultiplyByInteger(): void
    {
        $money = Money::of(3295, 'USD');

        self::assertSame(6590, $money->multiply(2)->amount());
    }

    #[DataProvider('ratioExamples')]
    public function testMultiplyRatio(int $amount, int $numerator, int $denominator, int $expected): void
    {
        $money = Money::of($amount, 'USD');

        self::assertSame($expected, $money->multiplyRatio($numerator, $denominator)->amount());
    }

    public static function ratioExamples(): array
    {
        return [
            'half even amount' => [100, 1, 2, 50],
            'half odd amount rounds up' => [99, 1, 2, 50],
            'thirds rounding' => [100, 1, 3, 33],
            'negative amount respects rounding' => [-100, 1, 2, -50],
        ];
    }

    public function testCompareDetectsCurrencyMismatch(): void
    {
        $this->expectException(CurrencyMismatch::class);

        Money::of(100, 'USD')->add(Money::of(100, 'EUR'));
    }

    public function testEquality(): void
    {
        self::assertTrue(Money::of(500, 'USD')->equals(Money::of(500, 'USD')));
        self::assertFalse(Money::of(500, 'USD')->equals(Money::of(501, 'USD')));
    }

    public function testFormat(): void
    {
        self::assertSame('USD 32.95', Money::of(3295, 'USD')->format());
        self::assertSame('USD -0.50', Money::of(-50, 'USD')->format());
    }
}
