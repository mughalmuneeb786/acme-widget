<?php

declare(strict_types=1);

namespace AcmeWidget\Interfaces;

/**
 * Interface for delivery cost calculation
 */
interface DeliveryCalculatorInterface
{
    public function calculateDelivery(float $subtotal): float;
}
