<?php

declare(strict_types=1);

namespace AcmeWidget;

use AcmeWidget\Interfaces\DeliveryCalculatorInterface;
use AcmeWidget\Interfaces\ProductCatalogInterface;
use AcmeWidget\Interfaces\OfferInterface;
use AcmeWidget\Offers\BuyOneGetOneHalfPriceOffer;

/**
 * Factory class for creating configured baskets with all dependencies
 */
final class BasketFactory
{
    private ProductCatalogInterface $catalog;
    private DeliveryCalculatorInterface $deliveryCalculator;
    /** @var array<OfferInterface> */
    private array $offers;

    public function __construct(
        ?ProductCatalogInterface $catalog = null,
        ?DeliveryCalculatorInterface $deliveryCalculator = null,
        ?array $offers = null
    ) {
        $this->catalog = $catalog ?? $this->createDefaultCatalog();
        $this->deliveryCalculator = $deliveryCalculator ?? TieredDeliveryCalculator::createAcmeRules();
        $this->offers = $offers ?? $this->createDefaultOffers();
    }

    public function createBasket(): Basket
    {
        return new Basket(
            $this->catalog,
            $this->deliveryCalculator,
            $this->offers
        );
    }

    public function setCatalog(ProductCatalogInterface $catalog): self
    {
        $this->catalog = $catalog;
        return $this;
    }

    public function setDeliveryCalculator(DeliveryCalculatorInterface $calculator): self
    {
        $this->deliveryCalculator = $calculator;
        return $this;
    }

    /**
     * @param array<OfferInterface> $offers
     */
    public function setOffers(array $offers): self
    {
        $this->offers = $offers;
        return $this;
    }

    private function createDefaultCatalog(): ProductCatalogInterface
    {
        return new ProductCatalog([
            new Product('R01', 'Red Widget', 32.95),
            new Product('G01', 'Green Widget', 24.95),
            new Product('B01', 'Blue Widget', 7.95),
        ]);
    }

    /**
     * @return array<OfferInterface>
     */
    private function createDefaultOffers(): array
    {
        return [
            new BuyOneGetOneHalfPriceOffer('R01'),
        ];
    }
}
