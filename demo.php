<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use AcmeWidget\BasketFactory;

/**
 * Command line demonstration of the basket system
 */
function main(): void
{
    $factory = new BasketFactory();
    echo "Acme Widget Co - Basket Demo\n";
    echo "============================\n\n";
    // Show delivery rules first
    displayDeliveryRules();
    // Test cases from the requirements
    $testCases = [
        ['B01', 'G01'],
        ['R01', 'R01'],
        ['R01', 'G01'],
        ['B01', 'B01', 'R01', 'R01', 'R01'],
    ];

    $expectedTotals = [37.85, 54.37, 60.85, 98.27];

    foreach ($testCases as $index => $products) {
        $basket = $factory->createBasket();
        echo "Test Case " . ($index + 1) . "\n";
        echo "Products: " . implode(', ', $products) . "\n";
        foreach ($products as $productCode) {
            $basket->add($productCode);
        }
        $total = $basket->total();
        $expected = $expectedTotals[$index];
        // Show detailed breakdown
        displayDetailedBreakdown($basket, $factory);
        echo "Expected Total: $" . number_format($expected, 2) . "\n";
        echo "Status: " . ($total === $expected ? "✅ PASS" : "❌ FAIL") . "\n";
        echo "\n" . str_repeat("-", 50) . "\n\n";
    }

    // Interactive mode
    echo "Interactive Mode - Available commands:\n";
    echo "Product codes: R01, G01, B01 (add to basket)\n";
    echo "1-'total' - Show detailed breakdown\n";
    echo "2-'delivery' - Show delivery calculation\n";
    echo "3-'clear' - Clear basket\n";
    echo "4-'help' - Show this help\n";
    echo "5-'exit' - Exit\n\n";
    $basket = $factory->createBasket();
    while (true) {
        echo "> ";
        $line = fgets(STDIN);
        if ($line === false) {
        // EOF or input error
        echo "\nNo input detected. Exiting.\n";
        break;
        }
        $input = trim($line);
        if ($input === 'r01' || $input === 'g01' || $input === 'b01') {
        // Skip empty input
        continue;
        }
        if (strtolower($input) === 'exit') {
            break;
        }
        if (strtolower($input) === 'total') {
            displayBasketSummary($basket, $factory);
            continue;
        }
        if (strtolower($input) === 'delivery') {
            displayDeliveryCalculation($basket, $factory);
            continue;
        }
        if (strtolower($input) === 'clear') {
            $basket->clear();
            echo "Basket cleared.\n";
            continue;
        }
        if (strtolower($input) === 'help') {
            echo "Available commands:\n";
            echo "R01, G01, B01 - Add product to basket\n";
            echo "total - Show detailed breakdown\n";
            echo "delivery - Show delivery calculation\n";
            echo "clear - Clear basket\n";
            echo "exit - Exit\n";
            continue;
        }
        try {
            $basket->add(strtoupper($input));
            echo "Added {$input} to basket.\n";

            // Show mini summary after adding
            $items = $basket->getItems();
            $totalItems = array_sum($items);
            echo "Basket now has {$totalItems} item(s). Type 'total' for full breakdown.\n";
        } catch (InvalidArgumentException $e) {
            echo "Error: {$e->getMessage()}\n";
            echo "Valid codes are: R01, G01, B01 (or type 'help' for commands)\n";
        }
    }
}

function displayDeliveryRules(): void
{
    echo "Delivery Rules:\n";
    echo "• Orders under $50.00: $4.95 delivery\n";
    echo "• Orders $50.00 - $89.99: $2.95 delivery\n";
    echo "• Orders $90.00 and above: FREE delivery\n\n";
    echo "Special Offers:\n";
    echo "• Red Widget (R01): Buy one, get second half price\n\n";
    echo "Product Prices:\n";
    echo "• Red Widget (R01): $32.95\n";
    echo "• Green Widget (G01): $24.95\n";
    echo "• Blue Widget (B01): $7.95\n\n";
    echo str_repeat("=", 50) . "\n\n";
}

function displayDetailedBreakdown(AcmeWidget\Basket $basket, AcmeWidget\BasketFactory $factory): void
{
    $items = $basket->getItems();
    // Get product catalogue to access product details
    $testBasket = $factory->createBasket();
    $subtotal = 0.0;
    $itemDetails = [];
    echo "Breakdown:\n";
    // Calculate subtotal and show item details
    foreach ($items as $code => $quantity) {
        // Get product price by adding to a test basket
        $tempBasket = $factory->createBasket();
        $tempBasket->add($code);
        $tempItems = $tempBasket->getItems();
        // Calculate price per item (this is a workaround to get product price)
        // In a real implementation, we'd access the catalogue directly
        switch ($code) {
            case 'R01':
                $price = 32.95;
                $name = 'Red Widget';
                break;
            case 'G01':
                $price = 24.95;
                $name = 'Green Widget';
                break;
            case 'B01':
                $price = 7.95;
                $name = 'Blue Widget';
                break;
            default:
                $price = 0.0;
                $name = 'Unknown Product';
        }
        $lineTotal = $price * $quantity;
        $subtotal += $lineTotal;
        $itemDetails[] = [
            'code' => $code,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'total' => $lineTotal];
        echo "{$quantity}x {$name} ({$code}) @ $" .
        number_format($price, 2) .
        " = $" . number_format($lineTotal, 2) . "\n";
    }
    echo "Subtotal: $" . number_format($subtotal, 2) . "\n";
    // Calculate and show discounts
    $discountAmount = calculateDiscounts($items);
    if ($discountAmount > 0) {
        echo "Discounts: -$" . number_format($discountAmount, 2) . "\n";
        showDiscountDetails($items);
    }
    $subtotalAfterDiscounts = $subtotal - $discountAmount;
    echo "After Discounts: $" . number_format($subtotalAfterDiscounts, 2) . "\n";
    // Calculate delivery
    $deliveryCost = calculateDelivery($subtotalAfterDiscounts);
    $deliveryText = getDeliveryText($subtotalAfterDiscounts);
    echo "Delivery: $" . number_format($deliveryCost, 2) . " {$deliveryText}\n";
    $finalTotal = $basket->total();
    echo "FINAL TOTAL: $" . number_format($finalTotal, 2) . "\n";
}

function displayBasketSummary(AcmeWidget\Basket $basket, AcmeWidget\BasketFactory $factory): void
{
    $items = $basket->getItems();
    if (empty($items)) {
        echo "Basket is empty.\n";
        // Show delivery cost for empty basket
        echo "Delivery cost for empty basket: $4.95\n";
        echo "Total: $4.95\n";
        return;
    }
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "BASKET SUMMARY\n";
    echo str_repeat("=", 40) . "\n";
    displayDetailedBreakdown($basket, $factory);
    echo str_repeat("=", 40) . "\n\n";
}

function displayDeliveryCalculation(AcmeWidget\Basket $basket, AcmeWidget\BasketFactory $factory): void
{
    $items = $basket->getItems();
    if (empty($items)) {
        echo "\nDelivery Calculation (Empty Basket):\n";
        echo "Subtotal: $0.00\n";
        echo "Rule: Orders under $50.00 → $4.95 delivery\n";
        echo "Delivery Cost: $4.95\n";
        return;
    }

    // Calculate subtotal after discounts
    $subtotal = 0.0;
    foreach ($items as $code => $quantity) {
        switch ($code) {
            case 'R01':
                $subtotal += 32.95 * $quantity;
                break;
            case 'G01':
                $subtotal += 24.95 * $quantity;
                break;
            case 'B01':
                $subtotal += 7.95 * $quantity;
                break;
        }
    }
    $discountAmount = calculateDiscounts($items);
    $subtotalAfterDiscounts = $subtotal - $discountAmount;
    $deliveryCost = calculateDelivery($subtotalAfterDiscounts);
    echo "\nDelivery Calculation:\n";
    echo "Subtotal after discounts: $" . number_format($subtotalAfterDiscounts, 2) . "\n";
    if ($subtotalAfterDiscounts >= 90.0) {
        echo "Rule: Orders $90.00+ → FREE delivery\n";
        echo "Delivery Cost: $0.00\n";
    } elseif ($subtotalAfterDiscounts >= 50.0) {
        echo "Rule: Orders $50.00-$89.99 → $2.95 delivery\n";
        echo "Delivery Cost: $2.95\n";
    } else {
        echo "Rule: Orders under $50.00 → $4.95 delivery\n";
        echo "Delivery Cost: $4.95\n";
    }
}

function calculateDiscounts(array $items): float
{
    $totalDiscount = 0.0;
    // BOGOHP for R01
    if (isset($items['R01']) && $items['R01'] >= 2) {
        $discountedItems = intval($items['R01'] / 2);
        $halfPrice = 32.95 / 2;
        $bogohpDiscount = round($discountedItems * $halfPrice, 2);
        $totalDiscount += $bogohpDiscount;
    }
    return $totalDiscount;
}

function showDiscountDetails(array $items): void
{
    if (isset($items['R01']) && $items['R01'] >= 2) {
        $discountedItems = intval($items['R01'] / 2);
        echo "BOGOHP on R01: {$discountedItems} item(s) at half price\n";
    }
}

function calculateDelivery(float $subtotal): float
{
    if ($subtotal >= 90.0) {
        return 0.0;
    } elseif ($subtotal >= 50.0) {
        return 2.95;
    } else {
        return 4.95;
    }
}

function getDeliveryText(float $subtotal): string
{
    if ($subtotal >= 90.0) {
        return "(FREE - over $90)";
    } elseif ($subtotal >= 50.0) {
        return "($50-$89.99 range)";
    } else {
        return "(under $50)";
    }
}

if (php_sapi_name() === 'cli') {
    main();
}
