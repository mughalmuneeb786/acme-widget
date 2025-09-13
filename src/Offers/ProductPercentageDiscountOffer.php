<?php

declare(strict_types=1);

namespace AcmeWidget\Offers;

use AcmeWidget\Interfaces\OfferInterface;
use AcmeWidget\Interfaces\ProductCatalogInterface;

/**
 * Percentage discount offer for a specific product
 */
final class ProductPercentageDiscountOffer implements OfferInterface
{
    public function __construct(
        private string $productCode,
        private float $discountPercentage,
        private int $minimumQuantity = 1
    ) {
        if (empty($productCode)) {
            throw new \InvalidArgumentException('Product code cannot be empty');
        }
        if ($discountPercentage < 0 || $discountPercentage > 100) {
            throw new \InvalidArgumentException('Discount percentage must be between 0 and 100');
        }
        if ($minimumQuantity < 1) {
            throw new \InvalidArgumentException('Minimum quantity must be at least 1');
        }
    }

    /**
     * @param array<string, int> $items
     * @param ProductCatalogInterface $catalog
     * @return float
     */
    public function calculateDiscount(array $items, ProductCatalogInterface $catalog): float
    {
        if (!isset($items[$this->productCode]) || $items[$this->productCode] < $this->minimumQuantity) {
            return 0.0;
        }

        $quantity = $items[$this->productCode];
        $product = $catalog->getProduct($this->productCode);
        $totalValue = $product->getPrice() * $quantity;
        $discount = $totalValue * ($this->discountPercentage / 100);
        // Round the discount to 2 decimal places to match expected behavior
        return round($discount, 2);
    }

    public function getDescription(): string
    {
        return sprintf(
            "%.1f%% off %s (minimum %d item%s)",
            $this->discountPercentage,
            $this->productCode,
            $this->minimumQuantity,
            $this->minimumQuantity > 1 ? 's' : ''
        );
    }
}
