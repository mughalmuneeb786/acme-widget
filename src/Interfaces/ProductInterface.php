<?php

declare(strict_types=1);

namespace AcmeWidget\Interfaces;

/**
 * Interface for product objects
 */
interface ProductInterface
{
    public function getCode(): string;
    public function getName(): string;
    public function getPrice(): float;
}
