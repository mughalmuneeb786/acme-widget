<?php

declare(strict_types=1);

namespace Tests;

use AcmeWidget\Basket;
use AcmeWidget\BasketFactory;
use AcmeWidget\Product;
use AcmeWidget\ProductCatalog;
use AcmeWidget\TieredDeliveryCalculator;
use AcmeWidget\Offers\ProductPercentageDiscountOffer;
use AcmeWidget\Offers\BuyOneGetOneHalfPriceOffer;
use PHPUnit\Framework\TestCase;

final class BasketFactoryTest extends TestCase
{
    public function testCreateBasketWithDefaults(): void
    {
        $factory = new BasketFactory();
        $basket = $factory->createBasket();
        $this->assertInstanceOf(Basket::class, $basket);
        // Test that default products exist
        $basket->add('R01');
        $basket->add('G01');
        $basket->add('B01');
        // Should not throw exception since products exist
        $this->assertCount(3, $basket->getItems());
    }

    public function testCreateBasketWithDefaultOffers(): void
    {
        $factory = new BasketFactory();
        $basket = $factory->createBasket();
        // Test that BOGOHP offer is active by default
        $basket->add('R01');
        $basket->add('R01');
        // With BOGOHP: 2 * 32.95 = 65.90, discount = 16.48, subtotal = 49.42, delivery = 4.95
        $this->assertEquals(54.37, $basket->total());
    }

    public function testCreateBasketWithDefaultDeliveryRules(): void
    {
        $factory = new BasketFactory();
        $basket = $factory->createBasket();
        // Test different delivery tiers
        $basket->add('B01'); // 7.95 + 4.95 delivery = 12.90
        $this->assertEquals(12.90, $basket->total());
        $basket->clear();
        $basket->add('G01');
        $basket->add('G01'); // 49.90 + 4.95 delivery = 54.85
        $this->assertEquals(54.85, $basket->total());
        $basket->clear();
        $basket->add('G01');
        $basket->add('G01');
        $basket->add('G01'); // 74.85 + 2.95 delivery = 77.80
        $this->assertEquals(77.80, $basket->total());
    }

    public function testSetCustomCatalog(): void
    {
        $customCatalog = new ProductCatalog([
            new Product('CUSTOM', 'Custom Product', 15.99)
        ]);
        $factory = new BasketFactory();
        $factory->setCatalog($customCatalog);
        $basket = $factory->createBasket();
        $basket->add('CUSTOM');
        $this->assertEquals(1, $basket->getItems()['CUSTOM']);
        // Should throw exception for default products
        $this->expectException(\InvalidArgumentException::class);
        $basket->add('R01');
    }

    public function testSetCustomDeliveryCalculator(): void
    {
        $customDelivery = new TieredDeliveryCalculator([
            ['threshold' => 0.0, 'cost' => 9.99]
        ]);
        $factory = new BasketFactory();
        $factory->setDeliveryCalculator($customDelivery);
        $basket = $factory->createBasket();
        $basket->add('B01'); // 7.95 + 9.99 delivery = 17.94
        $this->assertEquals(17.94, $basket->total());
    }

    public function testSetCustomOffers(): void
    {
        $customOffers = [
            new ProductPercentageDiscountOffer('R01', 25.0, 1)
        ];
        $factory = new BasketFactory();
        $factory->setOffers($customOffers);
        $basket = $factory->createBasket();
        $basket->add('R01'); // 32.95 * 75% = 24.71, + 4.95 delivery = 29.66
        $this->assertEquals(29.66, $basket->total());
    }

    public function testFluentInterface(): void
    {
        $customCatalog = new ProductCatalog([
            new Product('TEST', 'Test Product', 20.00)
        ]);
        $customDelivery = new TieredDeliveryCalculator([
            ['threshold' => 0.0, 'cost' => 5.00]
        ]);
        $customOffers = [
            new ProductPercentageDiscountOffer('TEST', 10.0, 1)
        ];
        $factory = new BasketFactory();
        $basket = $factory
            ->setCatalog($customCatalog)
            ->setDeliveryCalculator($customDelivery)
            ->setOffers($customOffers)
            ->createBasket();
        $basket->add('TEST'); // 20.00 * 90% = 18.00, + 5.00 delivery = 23.00
        $this->assertEquals(23.00, $basket->total());
    }

    public function testMultipleBasketCreation(): void
    {
        $factory = new BasketFactory();
        $basket1 = $factory->createBasket();
        $basket2 = $factory->createBasket();
        $this->assertInstanceOf(Basket::class, $basket1);
        $this->assertInstanceOf(Basket::class, $basket2);
        $this->assertNotSame($basket1, $basket2);
        // Each basket should be independent
        $basket1->add('R01');
        $this->assertCount(1, $basket1->getItems());
        $this->assertCount(0, $basket2->getItems());
    }

    public function testConstructorWithAllParameters(): void
    {
        $catalog = new ProductCatalog([
            new Product('SPECIAL', 'Special Product', 50.00)
        ]);
        $delivery = new TieredDeliveryCalculator([
            ['threshold' => 0.0, 'cost' => 2.50]
        ]);
        $offers = [
            new ProductPercentageDiscountOffer('SPECIAL', 20.0, 1)
        ];
        $factory = new BasketFactory($catalog, $delivery, $offers);
        $basket = $factory->createBasket();
        $basket->add('SPECIAL'); // 50.00 * 80% = 40.00, + 2.50 delivery = 42.50
        $this->assertEquals(42.50, $basket->total());
    }

    public function testConstructorWithNullParametersUsesDefaults(): void
    {
        $factory = new BasketFactory(null, null, null);
        $basket  =  $factory->createBasket();
        // Should work with default configuration
        $basket->add('R01');
        $basket->add('G01');
        $basket->add('B01');
        $this->assertCount(3, $basket->getItems());
        // Test that defaults are working
        $total = $basket->total();
        $this->assertGreaterThan(0, $total);
    }

    public function testEmptyOffersArray(): void
    {
        $factory = new BasketFactory();
        $factory->setOffers([]);
        $basket = $factory->createBasket();
        // Without offers, two red widgets should be full price
        $basket->add('R01');
        $basket->add('R01'); // 2 * 32.95 = 65.90, + 2.95 delivery = 68.85
        $this->assertEquals(68.85, $basket->total());
    }

    public function testMultipleOffers(): void
    {
        $offers  =  [
            new BuyOneGetOneHalfPriceOffer('R01'),
            new ProductPercentageDiscountOffer('G01', 15.0, 1)
        ];
        $factory = new BasketFactory();
        $factory->setOffers($offers);
        $basket = $factory->createBasket();
        $basket->add('R01');
        $basket->add('R01'); // BOGOHP applies
        $basket->add('G01'); // 15% discount applies
        // R01: 65.90 - 16.48 = 49.42
        // G01: 24.95 - 3.74 = 21.21
        // Subtotal: 70.63, delivery: 2.95, total: 73.58
        $this->assertEquals(73.58, $basket->total());
    }
}
