<?php

declare(strict_types=1);

namespace ArchitechLabs\Basket\Domain;

use ArchitechLabs\Basket\Domain\Exception\CurrencyMismatch;
use InvalidArgumentException;
use function abs;
use function sprintf;

/**
 * Simple value object that keeps money in integer cents to avoid floating point bugs.
 */
final class Money
{
    private int $amount;
    private string $currency;

    /**
     * Constructor is private so we can control how instances are created.
     */
    private function __construct(int $amount, string $currency)
    {
        $currency = strtoupper($currency);
        if ($currency === '') {
            throw new InvalidArgumentException('Currency must be a non-empty ISO code.');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Build a money value from the given cents and ISO currency code.
     */
    public static function of(int $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    /**
     * Handy helper for zero amounts in a currency.
     */
    public static function zero(string $currency): self
    {
        return new self(0, $currency);
    }

    /**
     * Raw integer amount in cents. Useful for formatting later.
     */
    public function amount(): int
    {
        return $this->amount;
    }

    /**
     * Return the ISO currency code in uppercase.
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Add another money value with the same currency.
     */
    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtract another money value with the same currency.
     */
    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    /**
     * Multiply the amount by a whole number. Safe for quantities of items.
     */
    public function multiply(int $multiplier): self
    {
        return new self($this->amount * $multiplier, $this->currency);
    }

    /**
     * Multiply by a rational number expressed as numerator/denominator.
     * Rounded to the nearest cent so we stay in integers.
     */
    public function multiplyRatio(int $numerator, int $denominator): self
    {
        if ($denominator <= 0) {
            throw new InvalidArgumentException('Denominator must be greater than zero.');
        }

        $dividend = $this->amount * $numerator;
        $quotient = intdiv($dividend, $denominator);
        $remainder = $dividend % $denominator;

        if ($remainder !== 0) {
            $adjustment = $this->shouldRoundUp($remainder, $denominator) ? 1 : 0;
            if ($adjustment !== 0) {
                $quotient += $dividend >= 0 ? $adjustment : -$adjustment;
            }
        }

        return new self($quotient, $this->currency);
    }

    /**
     * Compare amounts. Returns -1, 0 or 1 just like the spaceship operator.
     */
    public function compare(self $other): int
    {
        $this->assertSameCurrency($other);

        return $this->amount <=> $other->amount;
    }

    /**
     * Quick equality check for currency and amount.
     */
    public function equals(self $other): bool
    {
        return $this->compare($other) === 0;
    }

    /**
     * True when the amount is below zero.
     */
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    /**
     * True when the amount is above zero.
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Format the amount as a human friendly string with currency code.
     */
    public function format(): string
    {
        $absolute = abs($this->amount);
        $units = intdiv($absolute, 100);
        $cents = $absolute % 100;
        $sign = $this->amount < 0 ? '-' : '';

        return sprintf('%s %s%d.%02d', $this->currency, $sign, $units, $cents);
    }

    /**
     * Decide if we need to round up to the next cent in half-up style.
     */
    private function shouldRoundUp(int $remainder, int $denominator): bool
    {
        return abs($remainder) * 2 >= $denominator;
    }

    /**
     * Shared guard to make sure currency mismatches do not sneak in.
     */
    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw CurrencyMismatch::between($this->currency, $other->currency);
        }
    }
}
