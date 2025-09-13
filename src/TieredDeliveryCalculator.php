<?php

/**
 * Tiered delivery calculator based on order value
 */

declare(strict_types=1);

namespace AcmeWidget;

use AcmeWidget\Interfaces\DeliveryCalculatorInterface;

class TieredDeliveryCalculator implements DeliveryCalculatorInterface
{
    /**
     * @param array<array{threshold: float, cost: float}> $tiers Sorted by threshold descending
     */
    public function __construct(private array $tiers)
    {
        $this->validateTiers($tiers);
    }

    public function calculateDelivery(float $subtotal): float
    {
        foreach ($this->tiers as $tier) {
            if ($subtotal >= $tier['threshold']) {
                return $tier['cost'];
            }
        }

        // This should never happen with properly configured tiers
        throw new \RuntimeException('No delivery tier found for subtotal: ' . $subtotal);
    }

    /**
     * @param array<array{threshold: float, cost: float}> $tiers
     */
    private function validateTiers(array $tiers): void
    {
        if (empty($tiers)) {
            throw new \InvalidArgumentException('Delivery tiers cannot be empty');
        }

        $previousThreshold = PHP_FLOAT_MAX;
        foreach ($tiers as $tier) {
            if (!isset($tier['threshold'], $tier['cost'])) {
                throw new \InvalidArgumentException('Each tier must have threshold and cost keys');
            }
            if ($tier['threshold'] >= $previousThreshold) {
                throw new \InvalidArgumentException('Tiers must be sorted by threshold in descending order');
            }
            if ($tier['cost'] < 0) {
                throw new \InvalidArgumentException('Delivery cost cannot be negative');
            }
            $previousThreshold = $tier['threshold'];
        }
    }

    /**
     * Factory method for Acme Widget Co's delivery rules
     */
    public static function createAcmeRules(): self
    {
        return new self([
            ['threshold' => 90.0, 'cost' => 0.0],    // Free delivery for £90+
            ['threshold' => 50.0, 'cost' => 2.95],   // £2.95 for £50-£89.99
            ['threshold' => 0.0, 'cost' => 4.95],    // £4.95 for under £50
        ]);
    }
}
