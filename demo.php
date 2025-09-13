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
        
        echo "Calculated Total: $" . number_format($total, 2) . "\n";
        echo "Expected Total: $" . number_format($expected, 2) . "\n";
        echo "Status: " . ($total === $expected ? "✅ PASS" : "❌ FAIL") . "\n";
        echo "\n";
    }

    // Interactive mode
    echo "Interactive Mode - Enter product codes (R01, G01, B01) or 'quit' to exit:\n";
    $basket = $factory->createBasket();
    
    while (true) {
        echo "> ";
        $input = trim(fgets(STDIN));
        
        if (strtolower($input) === 'quit') {
            break;
        }
        
        if (strtolower($input) === 'total') {
            displayBasketSummary($basket);
            continue;
        }
        
        if (strtolower($input) === 'clear') {
            $basket->clear();
            echo "Basket cleared.\n";
            continue;
        }
        
        try {
            $basket->add(strtoupper($input));
            echo "Added {$input} to basket.\n";
        } catch (InvalidArgumentException $e) {
            echo "Error: {$e->getMessage()}\n";
            echo "Valid codes are: R01, G01, B01\n";
        }
    }
}

function displayBasketSummary(AcmeWidget\Basket $basket): void
{
    $items = $basket->getItems();
    
    if (empty($items)) {
        echo "Basket is empty.\n";
        return;
    }
    
    echo "Basket Contents:\n";
    foreach ($items as $code => $quantity) {
        echo "  {$code}: {$quantity}\n";
    }
    echo "Total: $" . number_format($basket->total(), 2) . "\n";
}

if (php_sapi_name() === 'cli') {
    main();
}