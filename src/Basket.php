<?php

declare(strict_types=1);

namespace AcmeWidget;

use AcmeWidget\Interfaces\DeliveryCalculatorInterface;
use AcmeWidget\Interfaces\OfferInterface;
use AcmeWidget\Interfaces\ProductCatalogInterface;

/**
 * Main basket implementation that handles adding products and calculating totals
 */
final class Basket
{
    /** @var array<string, int> */
    private array $items = [];

    /**
     * @param ProductCatalogInterface $catalog
     * @param DeliveryCalculatorInterface $deliveryCalculator
     * @param array<OfferInterface> $offers
     */
    public function __construct(
        private readonly ProductCatalogInterface $catalog,
        private readonly DeliveryCalculatorInterface $deliveryCalculator,
        private readonly array $offers = []
    ) {
    }

    /**
     * Add a product to the basket by its code
     */
    public function add(string $productCode): void
    {
        if (!$this->catalog->hasProduct($productCode)) {
            throw new \InvalidArgumentException("Product code '{$productCode}' not found in catalog");
        }

        $this->items[$productCode] = ($this->items[$productCode] ?? 0) + 1;
    }

    /**
     * Calculate the total cost including products, offers, and delivery
     */
    public function total(): float
    {
        $subtotal = $this->calculateSubtotal();
        $discountAmount = $this->calculateOfferDiscounts();
        $subtotalAfterOffers = $subtotal - $discountAmount;
        $deliveryCost = $this->deliveryCalculator->calculateDelivery($subtotalAfterOffers);

        $total = $subtotalAfterOffers + $deliveryCost;
        // Round the final total to 2 decimal places
        return round($total * 100) / 100;
    }

    /**
     * Get the current items in the basket
     * @return array<string, int>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Clear all items from the basket
     */
    public function clear(): void
    {
        $this->items = [];
    }

    private function calculateSubtotal(): float
    {
        $subtotal = 0.0;
        foreach ($this->items as $productCode => $quantity) {
            $product = $this->catalog->getProduct($productCode);
            $subtotal += $product->getPrice() * $quantity;
        }

        return $subtotal;
    }

    private function calculateOfferDiscounts(): float
    {
        $totalDiscount = 0.0;

        foreach ($this->offers as $offer) {
            $totalDiscount += $offer->calculateDiscount($this->items, $this->catalog);
        }

        return $totalDiscount;
    }
}
