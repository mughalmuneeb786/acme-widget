<?php

declare(strict_types=1);

namespace Tests;

use AcmeWidget\Product;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testProductCreation(): void
    {
        $product = new Product('R01', 'Red Widget', 32.95);
        $this->assertEquals('R01', $product->getCode());
        $this->assertEquals('Red Widget', $product->getName());
        $this->assertEquals(32.95, $product->getPrice());
    }

    public function testProductWithNegativePriceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product price cannot be negative');
        new Product('R01', 'Red Widget', -1.0);
    }

    public function testProductWithEmptyCodeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product code cannot be empty');
        new Product('', 'Red Widget', 32.95);
    }

    public function testProductWithEmptyNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty');
        new Product('R01', '', 32.95);
    }
}
