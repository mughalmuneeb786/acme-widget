<?php

declare(strict_types=1);

namespace Tests;

use AcmeWidget\Product;
use AcmeWidget\ProductCatalog;
use PHPUnit\Framework\TestCase;

final class ProductCatalogTest extends TestCase
{
    public function testEmptyCatalogCreation(): void
    {
        $catalog = new ProductCatalog();
        $this->assertEmpty($catalog->getAllProducts());
        $this->assertFalse($catalog->hasProduct('R01'));
    }

    public function testCatalogWithInitialProducts(): void
    {
        $products = [
            new Product('R01', 'Red Widget', 32.95),
            new Product('G01', 'Green Widget', 24.95),
        ];
        $catalog = new ProductCatalog($products);
        $this->assertCount(2, $catalog->getAllProducts());
        $this->assertTrue($catalog->hasProduct('R01'));
        $this->assertTrue($catalog->hasProduct('G01'));
        $this->assertFalse($catalog->hasProduct('B01'));
    }

    public function testAddProduct(): void
    {
        $catalog = new ProductCatalog();
        $product = new Product('R01', 'Red Widget', 32.95);
        $catalog->addProduct($product);
        $this->assertTrue($catalog->hasProduct('R01'));
        $this->assertSame($product, $catalog->getProduct('R01'));
    }

    public function testGetExistingProduct(): void
    {
        $product = new Product('R01', 'Red Widget', 32.95);
        $catalog = new ProductCatalog([$product]);
        $retrievedProduct = $catalog->getProduct('R01');
        $this->assertSame($product, $retrievedProduct);
        $this->assertEquals('R01', $retrievedProduct->getCode());
        $this->assertEquals('Red Widget', $retrievedProduct->getName());
        $this->assertEquals(32.95, $retrievedProduct->getPrice());
    }

    public function testGetNonExistentProductThrowsException(): void
    {
        $catalog = new ProductCatalog();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Product with code 'INVALID' not found");
        $catalog->getProduct('INVALID');
    }

    public function testHasProductReturnsTrueForExistingProduct(): void
    {
        $product = new Product('R01', 'Red Widget', 32.95);
        $catalog = new ProductCatalog([$product]);
        $this->assertTrue($catalog->hasProduct('R01'));
    }

    public function testHasProductReturnsFalseForNonExistentProduct(): void
    {
        $catalog = new ProductCatalog();
        $this->assertFalse($catalog->hasProduct('INVALID'));
    }

    public function testGetAllProductsReturnsCorrectArray(): void
    {
        $redWidget = new Product('R01', 'Red Widget', 32.95);
        $greenWidget = new Product('G01', 'Green Widget', 24.95);
        $blueWidget = new Product('B01', 'Blue Widget', 7.95);
        $catalog = new ProductCatalog([$redWidget, $greenWidget]);
        $catalog->addProduct($blueWidget);
        $allProducts = $catalog->getAllProducts();
        $this->assertCount(3, $allProducts);
        $this->assertArrayHasKey('R01', $allProducts);
        $this->assertArrayHasKey('G01', $allProducts);
        $this->assertArrayHasKey('B01', $allProducts);
        $this->assertSame($redWidget, $allProducts['R01']);
        $this->assertSame($greenWidget, $allProducts['G01']);
        $this->assertSame($blueWidget, $allProducts['B01']);
    }

    public function testAddingProductWithSameCodeOverwrites(): void
    {
        $originalProduct = new Product('R01', 'Original Red Widget', 30.00);
        $newProduct = new Product('R01', 'New Red Widget', 35.00);
        $catalog = new ProductCatalog([$originalProduct]);
        $this->assertEquals(30.00, $catalog->getProduct('R01')->getPrice());
        $catalog->addProduct($newProduct);
        $this->assertEquals(35.00, $catalog->getProduct('R01')->getPrice());
        $this->assertEquals('New Red Widget', $catalog->getProduct('R01')->getName());
        $this->assertSame($newProduct, $catalog->getProduct('R01'));
    }

    public function testCasesensitivity(): void
    {
        $product = new Product('r01', 'Lowercase Red Widget', 32.95);
        $catalog = new ProductCatalog([$product]);
        $this->assertTrue($catalog->hasProduct('r01'));
        $this->assertFalse($catalog->hasProduct('R01'));
        $this->assertFalse($catalog->hasProduct('r01' . strtoupper('x')));
    }

    public function testImmutableReturnFromGetAllProducts(): void
    {
        $product = new Product('R01', 'Red Widget', 32.95);
        $catalog = new ProductCatalog([$product]);
        $products = $catalog->getAllProducts();
        unset($products['R01']); // Modify the returned array
        // Original catalog should be unchanged
        $this->assertTrue($catalog->hasProduct('R01'));
        $this->assertCount(1, $catalog->getAllProducts());
    }
}
