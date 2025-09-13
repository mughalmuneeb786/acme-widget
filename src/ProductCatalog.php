<?php

declare(strict_types=1);

namespace AcmeWidget;

use AcmeWidget\Interfaces\ProductInterface;
use AcmeWidget\Interfaces\ProductCatalogInterface;

/**
 * In-memory product catalog implementation
 */
class ProductCatalog implements ProductCatalogInterface
{
    /** @var array<string, ProductInterface> */
    private array $products = [];

    /**
     * @param array<ProductInterface> $products
     */
    public function __construct(array $products = [])
    {
        foreach ($products as $product) {
            $this->addProduct($product);
        }
    }

    public function addProduct(ProductInterface $product): void
    {
        $this->products[$product->getCode()] = $product;
    }

    public function hasProduct(string $code): bool
    {
        return array_key_exists($code, $this->products);
    }

    public function getProduct(string $code): ProductInterface
    {
        if (!$this->hasProduct($code)) {
            throw new \InvalidArgumentException("Product with code '{$code}' not found");
        }

        return $this->products[$code];
    }

    /**
     * @return array<string, ProductInterface>
     */
    public function getAllProducts(): array
    {
        return $this->products;
    }
}
