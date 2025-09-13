<?php

declare(strict_types=1);

namespace AcmeWidget\Offers;

use AcmeWidget\Interfaces\OfferInterface;
use AcmeWidget\Interfaces\ProductCatalogInterface;

/**
 * Buy-one-get-one-half-price offer for a specific product
 */
final class BuyOneGetOneHalfPriceOffer implements OfferInterface
{
    public function __construct(private string $productCode)
    {
        if (empty($productCode)) {
            throw new \InvalidArgumentException('Product code cannot be empty');
        }
    }

    /**
     * @param array<string, int> $items
     * @param ProductCatalogInterface $catalog
     * @return float
     */
    public function calculateDiscount(array $items, ProductCatalogInterface $catalog): float
    {
        if (!isset($items[$this->productCode]) || $items[$this->productCode] < 2) {
            return 0.0;
        }

        $quantity = $items[$this->productCode];
        $product = $catalog->getProduct($this->productCode);
        // Calculate how many items get the half-price discount
        // For every 2 items, 1 gets half price
        $discountedItems = intval($quantity / 2);
        $halfPrice = $product->getPrice() / 2;
        // Round the discount to 2 decimal places to match expected behavior
        return round($discountedItems * $halfPrice, 2);
    }

    public function getDescription(): string
    {
        return "Buy one {$this->productCode}, get the second half price";
    }
}
