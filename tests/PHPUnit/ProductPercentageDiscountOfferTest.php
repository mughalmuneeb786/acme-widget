<?php

declare(strict_types=1);

namespace Tests;

use AcmeWidget\ProductCatalog;
use AcmeWidget\Product;
use AcmeWidget\Offers\ProductPercentageDiscountOffer;
use PHPUnit\Framework\TestCase;

final class ProductPercentageDiscountOfferTest extends TestCase
{
    private ProductCatalog $catalog;

    protected function setUp(): void
    {
        $this->catalog = new ProductCatalog([
            new Product('TEST', 'Test Product', 10.00),
            new Product('EXPENSIVE', 'Expensive Product', 100.00),
        ]);
    }

    public function testOfferWithMinimumQuantity(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 20.0, 2);
        $items = ['TEST' => 2];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        // 2 * £10 * 20% = £4.00
        $this->assertEquals(4.00, $discount);
    }

    public function testOfferWithMoreThanMinimumQuantity(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 15.0, 2);
        $items = ['TEST' => 5];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        // 5 * £10 * 15% = £7.50
        $this->assertEquals(7.50, $discount);
    }

    public function testOfferWithLessThanMinimumQuantity(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 20.0, 3);
        $items = ['TEST' => 2];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        $this->assertEquals(0.0, $discount);
    }

    public function testOfferWithNoItems(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 20.0, 1);
        $items = [];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        $this->assertEquals(0.0, $discount);
    }

    public function testOfferWithDifferentProduct(): void
    {
        $offer = new ProductPercentageDiscountOffer('EXPENSIVE', 10.0, 1);
        $items = ['TEST' => 5];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        $this->assertEquals(0.0, $discount);
    }

    public function testOfferWithDefaultMinimumQuantity(): void
    {
        // Default minimum quantity is 1
        $offer = new ProductPercentageDiscountOffer('TEST', 25.0);
        $items = ['TEST' => 1];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        // 1 * £10 * 25% = £2.50
        $this->assertEquals(2.50, $discount);
    }

    public function testOfferWithHighPercentage(): void
    {
        $offer = new ProductPercentageDiscountOffer('EXPENSIVE', 50.0, 1);
        $items = ['EXPENSIVE' => 1];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        // 1 * £100 * 50% = £50.00
        $this->assertEquals(50.00, $discount);
    }

    public function testOfferWithLowPercentage(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 1.0, 1);
        $items = ['TEST' => 1];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        // 1 * £10 * 1% = £0.10
        $this->assertEquals(0.10, $discount);
    }

    public function testOfferWithRoundingRequired(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 33.333, 1);
        $items = ['TEST' => 1];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        // 1 * £10 * 33.333% = £3.3333, rounded to £3.33
        $this->assertEquals(3.33, $discount);
    }

    public function testOfferDescription(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 20.0, 2);
        $expected = '20.0% off TEST (minimum 2 items)';
        $this->assertEquals($expected, $offer->getDescription());
    }

    public function testOfferDescriptionWithSingularMinimum(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 15.0, 1);
        $expected = '15.0% off TEST (minimum 1 item)';
        $this->assertEquals($expected, $offer->getDescription());
    }

    public function testOfferDescriptionWithPluralMinimum(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 25.0, 5);
        $expected = '25.0% off TEST (minimum 5 items)';
        $this->assertEquals($expected, $offer->getDescription());
    }

    public function testEmptyProductCodeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product code cannot be empty');
        new ProductPercentageDiscountOffer('', 20.0, 1);
    }

    public function testNegativeDiscountPercentageThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount percentage must be between 0 and 100');
        new ProductPercentageDiscountOffer('TEST', -5.0, 1);
    }

    public function testDiscountPercentageOver100ThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount percentage must be between 0 and 100');
        new ProductPercentageDiscountOffer('TEST', 150.0, 1);
    }

    public function testZeroDiscountPercentageIsValid(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 0.0, 1);
        $items = ['TEST' => 5];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        $this->assertEquals(0.0, $discount);
    }

    public function test100PercentDiscountIsValid(): void
    {
        $offer = new ProductPercentageDiscountOffer('TEST', 100.0, 1);
        $items = ['TEST' => 1];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        // 1 * £10 * 100% = £10.00
        $this->assertEquals(10.00, $discount);
    }

    public function testMinimumQuantityZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Minimum quantity must be at least 1');
        new ProductPercentageDiscountOffer('TEST', 20.0, 0);
    }

    public function testNegativeMinimumQuantityThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Minimum quantity must be at least 1');
        new ProductPercentageDiscountOffer('TEST', 20.0, -1);
    }

    public function testOfferWithMultipleProducts(): void
    {
        // Offer only applies to specific product, not others in basket
        $offer = new ProductPercentageDiscountOffer('TEST', 20.0, 1);
        $items = ['TEST' => 2, 'EXPENSIVE' => 3];
        $discount = $offer->calculateDiscount($items, $this->catalog);
        // Only applies to TEST products: 2 * £10 * 20% = £4.00
        $this->assertEquals(4.00, $discount);
    }
}
