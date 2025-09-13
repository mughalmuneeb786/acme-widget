<?php

declare(strict_types=1);

namespace AcmeWidget;

use AcmeWidget\Interfaces\ProductInterface;

/**
 * Simple product implementation
 */
class Product implements ProductInterface
{
    public function __construct(
        private string $code,
        private string $name,
        private float $price
    ) {
        if ($price < 0) {
            throw new \InvalidArgumentException('Product price cannot be negative');
        }
        if (empty($code)) {
            throw new \InvalidArgumentException('Product code cannot be empty');
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
