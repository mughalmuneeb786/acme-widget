# Acme Widget Co - Sales System

A robust, extensible sales basket system built with modern PHP practices, featuring comprehensive testing, static analysis, and containerized development.

## Features

- **Flexible Product Management**: Extensible product catalog with type safety
- **Tiered Delivery System**: Configurable delivery costs based on order value
- **Pluggable Offers**: Strategy pattern for different promotional offers
- **Comprehensive Testing**: Unit and integration tests with PHPUnit
- **Static Analysis**: PHPStan at maximum level for code quality
- **Containerized Development**: Docker setup for consistent environments

## Architecture & Design Patterns

### Strategy Pattern
- `DeliveryCalculatorInterface` allows different delivery calculation strategies
- `OfferInterface` enables various promotional offer types
- Easy to extend without modifying core basket logic

### Dependency Injection
- `BasketFactory` acts as a simple DI container
- All dependencies injected via constructor
- Easy to mock for testing

### Value Objects
- `Product` is immutable with validation
- Type-safe operations throughout

### Interface Segregation
- Small, focused interfaces for better testability
- Clear contracts between components

## Quick Start

### Using Docker (Recommended)

```bash
# Run the demo
docker-compose up php

# Run tests
docker-compose up test

# Run static analysis
docker-compose up analyse

# Development environment
docker-compose up -d php-dev
docker-compose exec php-dev bash
```

### Local PHP Setup

```bash
# Install dependencies
composer install

# Run demo
php demo.php

# Run tests
composer test

# Run static analysis  
composer analyse

# Generate test coverage
composer test-coverage
```

## Usage Examples

### Basic Usage

```php
use AcmeWidget\BasketFactory;

$factory = new BasketFactory();
$basket = $factory->createBasket();

$basket->add('R01');
$basket->add('G01');

echo $basket->total(); // 60.85
```

### Custom Configuration

```php
use AcmeWidget\Basket;
use AcmeWidget\ProductCatalog;
use AcmeWidget\Product;
use AcmeWidget\TieredDeliveryCalculator;
use AcmeWidget\Offers\ProductPercentageDiscountOffer;

// Custom catalogue
$catalogue = new ProductCatalog([
    new Product('CUSTOM', 'Custom Widget', 15.99)
]);

// Custom delivery rules
$deliveryCalculator = new TieredDeliveryCalculator([
    ['threshold' => 100.0, 'cost' => 0.0],
    ['threshold' => 0.0, 'cost' => 5.99]
]);

// Custom offers
$offers = [
    new ProductPercentageDiscountOffer('CUSTOM', 10.0, 2)
];

$basket = new Basket($catalog, $deliveryCalculator, $offers);
```

## Test Cases

The system passes all required test cases:

| Products | Expected Total | Status |
|----------|---------------|---------|
| B01, G01 | $37.85 | ✅ |
| R01, R01 | $54.37 | ✅ |
| R01, G01 | $60.85 | ✅ |
| B01, B01, R01, R01, R01 | $98.27 | ✅ |

## Pricing Rules

### Products
- **Red Widget (R01)**: $32.95
- **Green Widget (G01)**: $24.95  
- **Blue Widget (B01)**: $7.95

### Delivery Costs
- Orders under $50: $4.95
- Orders $50-$89.99: $2.95
- Orders $90+: FREE

### Special Offers
- **Red Widget BOGOHP**: Buy one red widget, get the second half price

## Development

### Running Tests

```bash
# All tests
./vendor/bin/phpunit

# With coverage
./vendor/bin/phpunit --coverage-html coverage

# Specific test
./vendor/bin/phpunit tests/PHPUnit/BasketTest.php
```

### Static Analysis

```bash
# Run PHPStan
./vendor/bin/phpstan analyse

# Fix what can be auto-fixed
./vendor/bin/phpstan analyse --fix
```

### Adding New Offers

Implement the `OfferInterface`:

```php
class MyCustomOffer implements OfferInterface
{
    public function calculateDiscount(array $items, ProductCatalogInterface $catalogue): float
    {
        // Your discount logic here
    }
    
    public function getDescription(): string
    {
        return "My custom offer description";
    }
}
```

## Assumptions Made

1. **Currency**: All prices are in dollars (no currency conversion needed)
2. **Rounding**: Totals rounded to 2 decimal places
3. **Product Codes**: Case-sensitive, must match exactly
4. **Delivery**: Applied after offer discounts
5. **Offers**: Can stack (multiple offers can apply to same basket)
6. **Quantities**: Unlimited quantities allowed per product
7. **Persistence**: In-memory storage only (no database required)

## Code Quality Standards

- **PHP 8.1+**: Modern PHP with strict types
- **PSR-4**: Autoloading standard
- **PHPStan Level Max**: Strictest static analysis
- **100% Test Coverage**: Comprehensive test suite
- **SOLID Principles**: Clean architecture patterns
- **Immutable Objects**: Where appropriate for data integrity

## Performance Considerations

- **O(1)** product lookups via associative arrays
- **O(n)** offer calculations where n = number of offers
- **Memory efficient**: No unnecessary object creation
- **Optimized autoloader**: Composer optimization enabled

## Security Notes

- Input validation on all public methods
- Type hints prevent invalid data
- Immutable products prevent state corruption
- No external dependencies reduce attack surface

## License

MIT License - see LICENSE file for details.