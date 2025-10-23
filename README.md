# Basket Pricing Engine (PHP)

Command-line proof of concept for the Architech Labs basket pricing exercise. The domain is modelled with strict types, a Money value object, and strategy-based pricing that keeps offers and delivery rules pluggable.

## Problem Recap

- Products: `R01` = $32.95, `G01` = $24.95, `B01` = $7.95.
- Delivery: subtotal < $50 → $4.95, subtotal < $90 → $2.95, otherwise free.
- Offer: “Buy one red (`R01`), get the second half price” — discount applies per pair.
- Basket API: `add(code)` and `total()` returning subtotal, discount, delivery, total as Money objects.

Assumptions:

- All calculations stay in integer cents (`Money::of(3295, 'USD')`). Formatting only happens at the CLI/edge.
- Offer and delivery are applied after all items are in the basket; delivery is calculated after discount.
- Unknown product codes raise a domain exception; codes are case-insensitive.
- Empty basket totals are all zero.

## Getting Started

```bash
composer install
composer test
composer stan
```

- `composer test` runs PHPUnit (strict mode, 27 tests).
- `composer stan` runs PHPStan at level 7 with extra strictness toggles.
- `composer fix` (FriendsofPHP/PHP-CS-Fixer) is ready if needed.

### Demo CLI

Use the CLI helper to see the full basket breakdown:

```bash
php bin/demo.php R01,G01
php bin/demo.php B01 B01 R01 R01 R01
```

Sample outputs:

```
$ php bin/demo.php B01,G01
Basket items: B01, G01
Subtotal: USD 32.90
Discount: USD 0.00
Delivery: USD 4.95
Total:    USD 37.85

$ php bin/demo.php R01,R01
Basket items: R01, R01
Subtotal: USD 65.90
Discount: USD 16.48
Delivery: USD 4.95
Total:    USD 54.37

$ php bin/demo.php R01,G01
Basket items: R01, G01
Subtotal: USD 57.90
Discount: USD 0.00
Delivery: USD 2.95
Total:    USD 60.85

$ php bin/demo.php B01,B01,R01,R01,R01
Basket items: B01, B01, R01, R01, R01
Subtotal: USD 114.75
Discount: USD 16.48
Delivery: USD 0.00
Total:    USD 98.27
```

### Optional Docker

A lightweight container is included for parity with reviewer setups:

```bash
docker compose -f docker/docker-compose.yml run --rm app composer install
docker compose -f docker/docker-compose.yml run --rm app composer test
```

The image ships PHP 8.2 CLI, Composer, and Xdebug (disabled by default).

## Design Notes

- **Domain layer** (`src/Domain`): immutable `Money`, `Product`, and `Catalog` classes, plus domain exceptions. `Money` supports integer arithmetic, rounding by rational numbers, and formatted output.
- **Pricing layer** (`src/Pricing`): `Offer` interface, `RedSecondHalfPrice` implementation, delivery strategy modelled as sorted tiers, and `LineItems` helper for offers/fees.
- **Basket orchestration** (`src/Basket.php`): orchestrates catalog lookups, applies offers, then delivery. Returns a typed breakdown (`subtotal`, `discount`, `delivery`, `total`).
- **Tests** (`tests`): PHPUnit coverage for value objects, strategy behaviours, and basket acceptance scenarios (matching the four required totals plus error handling).

Extending the system:

- Add a new offer by implementing `Offer` and injecting it into the basket.
- Add delivery tiers by adjusting the `DeliveryRules` configuration (no code changes).
- Replace the CLI with a web/API layer by reusing the Basket service.
- Core calculations run in linear time relative to items in the basket; no global state makes it safe to reuse the services across requests.

## Project Tooling

- Coding standards: PSR-12 enforced via `.editorconfig` and optional `php-cs-fixer`.
- Static analysis: PHPStan level 7 (`phpstan.neon`).
- Tests: PHPUnit 10 (`phpunit.xml`).
- Docker: `docker/Dockerfile` + `docker/docker-compose.yml`.

## Next Steps

- Publish to a public repository with Conventional Commits.
- Consider richer money formatting (locale-aware) if a UI is added.
