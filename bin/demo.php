#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ArchitechLabs\Basket\Basket;
use ArchitechLabs\Basket\Domain\Catalog;
use ArchitechLabs\Basket\Domain\Money;
use ArchitechLabs\Basket\Domain\Product;
use ArchitechLabs\Basket\Pricing\DeliveryRules;
use ArchitechLabs\Basket\Pricing\DeliveryTier;
use ArchitechLabs\Basket\Pricing\RedSecondHalfPrice;

/**
 * Simple CLI entrypoint to exercise the basket totals for reviewers.
 */
final class DemoApplication
{
    /**
     * @param list<string> $codes
     */
    public function run(array $codes): void
    {
        $basket = $this->createBasket();

        foreach ($codes as $code) {
            $basket->add($code);
        }

        $summary = $basket->total();

        $this->renderSummary($codes, $summary);
    }

    /**
     * Helper that prints the breakdown in a readable way.
     *
     * @param array{subtotal: Money, discount: Money, delivery: Money, total: Money} $summary
     */
    private function renderSummary(array $codes, array $summary): void
    {
        $items = $codes === [] ? '[empty]' : implode(', ', $codes);

        echo "Basket items: {$items}" . PHP_EOL;
        echo 'Subtotal: ' . $summary['subtotal']->format() . PHP_EOL;
        echo 'Discount: ' . $summary['discount']->format() . PHP_EOL;
        echo 'Delivery: ' . $summary['delivery']->format() . PHP_EOL;
        echo 'Total:    ' . $summary['total']->format() . PHP_EOL;
    }

    private function createBasket(): Basket
    {
        $catalog = new Catalog([
            new Product('R01', 'Red Widget', Money::of(3295, 'USD')),
            new Product('G01', 'Green Widget', Money::of(2495, 'USD')),
            new Product('B01', 'Blue Widget', Money::of(795, 'USD')),
        ]);

        $offer = new RedSecondHalfPrice('R01');

        $deliveryRules = new DeliveryRules([
            new DeliveryTier(Money::of(5000, 'USD'), Money::of(495, 'USD')),
            new DeliveryTier(Money::of(9000, 'USD'), Money::of(295, 'USD')),
            new DeliveryTier(null, Money::of(0, 'USD')),
        ]);

        return new Basket($catalog, $offer, $deliveryRules, 'USD');
    }
}

/**
 * Bootstrap and input handling.
 */
final class DemoRunner
{
    public function __construct(private readonly DemoApplication $app)
    {
    }

    public function __invoke(array $argv): void
    {
        $codes = $this->parseCodes($argv);
        $this->app->run($codes);
    }

    /**
     * Parse comma-separated codes from CLI arguments.
     *
     * @return list<string>
     */
    private function parseCodes(array $argv): array
    {
        if (count($argv) < 2) {
            $this->printHelp();

            return [];
        }

        $raw = $argv[1];
        if (str_contains($raw, ',')) {
            $parts = array_map('trim', explode(',', $raw));
        } else {
            $parts = array_slice($argv, 1);
        }

        return array_values(array_filter($parts, static fn (string $code): bool => $code !== ''));
    }

    private function printHelp(): void
    {
        echo 'Usage: php bin/demo.php R01,G01' . PHP_EOL;
        echo '   or: php bin/demo.php R01 G01' . PHP_EOL;
        echo 'When no codes are provided the basket is empty.' . PHP_EOL;
    }
}

(new DemoRunner(new DemoApplication()))($argv);
