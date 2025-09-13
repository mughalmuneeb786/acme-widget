<?php

declare(strict_types=1);

namespace AcmeWidget\Interfaces;

/**
 * Interface for special offers
 */
interface OfferInterface
{
    /**
     * Calculate discount amount for given basket items
     * @param array<string, int> $items
     * @param ProductCatalogInterface $catalog
     * @return float
     */
    public function calculateDiscount(array $items, ProductCatalogInterface $catalog): float;
    public function getDescription(): string;
}
