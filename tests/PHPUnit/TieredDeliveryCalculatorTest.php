<?php

declare(strict_types=1);

namespace Tests;

use AcmeWidget\TieredDeliveryCalculator;
use PHPUnit\Framework\TestCase;

final class TieredDeliveryCalculatorTest extends TestCase
{
    public function testAcmeDeliveryRules(): void
    {
        $calculator = TieredDeliveryCalculator::createAcmeRules();
        $this->assertEquals(4.95, $calculator->calculateDelivery(0.0));
        $this->assertEquals(4.95, $calculator->calculateDelivery(49.99));
        $this->assertEquals(2.95, $calculator->calculateDelivery(50.0));
        $this->assertEquals(2.95, $calculator->calculateDelivery(89.99));
        $this->assertEquals(0.0, $calculator->calculateDelivery(90.0));
        $this->assertEquals(0.0, $calculator->calculateDelivery(100.0));
    }

    public function testEmptyTiersThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery tiers cannot be empty');
        new TieredDeliveryCalculator([]);
    }
}
