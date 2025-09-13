<?php

declare(strict_types=1);

namespace Tests;

use AcmeWidget\Basket;
use AcmeWidget\BasketFactory;
use AcmeWidget\Product;
use AcmeWidget\ProductCatalog;
use AcmeWidget\TieredDeliveryCalculator;
use AcmeWidget\Offers\ProductPercentageDiscountOffer;
use PHPUnit\Framework\TestCase;

final class BasketTest extends TestCase
{
    private BasketFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new BasketFactory();
    }

    /**
     * @dataProvider basketTestCasesProvider
     */
    public function testBasketTotals(array $products, float $expectedTotal): void
    {
        $basket = $this->factory->createBasket();
        foreach ($products as $productCode) {
            $basket->add($productCode);
        }
        $this->assertEquals($expectedTotal, $basket->total());
    }

    public static function basketTestCasesProvider(): array
    {
        return [
            'B01, G01' => [['B01', 'G01'], 37.85],
            'R01, R01' => [['R01', 'R01'], 54.37],
            'R01, G01' => [['R01', 'G01'], 60.85],
            'B01, B01, R01, R01, R01' => [['B01', 'B01', 'R01', 'R01', 'R01'], 98.27],

        ];
    }

    public function testAddInvalidProductThrowsException(): void
    {
        $basket = $this->factory->createBasket();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Product code 'INVALID' not found in catalog");
        $basket->add('INVALID');
    }

    public function testEmptyBasketTotal(): void
    {
        $basket = $this->factory->createBasket();
        // Empty basket should only have delivery cost
        $this->assertEquals(4.95, $basket->total());
    }

    public function testBasketCanBeCleared(): void
    {
        $basket = $this->factory->createBasket();
        $basket->add('R01');
        $basket->add('G01');
        $this->assertNotEmpty($basket->getItems());
        $basket->clear();
        $this->assertEmpty($basket->getItems());
    }

    public function testGetItemsReturnsCorrectQuantities(): void
    {
        $basket = $this->factory->createBasket();
        $basket->add('R01');
        $basket->add('R01');
        $basket->add('G01');
        $expected = ['R01' => 2, 'G01' => 1];
        $this->assertEquals($expected, $basket->getItems());
    }

    public function testCustomOffers(): void
    {
        $catalog = new ProductCatalog([
            new Product('TEST', 'Test Product', 10.0),
        ]);
        $deliveryCalculator = TieredDeliveryCalculator::createAcmeRules();
        $offers = [new ProductPercentageDiscountOffer('TEST', 20.0, 2)];
        $basket = new Basket($catalog, $deliveryCalculator, $offers);
        $basket->add('TEST');
        $basket->add('TEST');
        // 2 * £10 = £20, 20% discount = £4.00, subtotal = £16, delivery = £4.95
        $this->assertEquals(20.95, $basket->total());
    }
}
