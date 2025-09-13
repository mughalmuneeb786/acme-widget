<?php

declare(strict_types=1);

namespace AcmeWidget\Interfaces;

/**
 * Interface for product catalog
 */
interface ProductCatalogInterface
{
    public function hasProduct(string $code): bool;
    public function getProduct(string $code): ProductInterface;
    /**
     * @return array<string, ProductInterface>
     */
    public function getAllProducts(): array;
}
