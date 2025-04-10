<?php
// Define ROOT_PATH if not defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Include required files directly
require_once __DIR__ . '/includes/BettingSystem.php';
require_once __DIR__ . '/includes/BetParser.php';

// Example inputs to test
$inputs = [
    "Input 1" => "D\n#1234\n1234#1#1#1#1#1",
    "Input 2" => "D2\n#1\n1234#1#1",
    "Input 3" => "D3\n#12\n1234##1##1"
];

echo "=== BETTING SYSTEM TEST ===\n\n";

// Test with BettingSystem directly
echo "=== TESTING WITH BETTINGSYSTEM DIRECTLY ===\n\n";
foreach ($inputs as $label => $input) {
    echo "=== $label ===\n";
    
    $bettingSystem = new BettingSystem($input);
    echo $bettingSystem->formatBet();
    
    $data = $bettingSystem->getBettingData();
    echo "Total Amount: " . $data['totalAmount'] . "\n";
    echo "Periods: " . $data['periods'] . "\n";
    echo "Lottery Types: " . implode(", ", $data['lotteryTypes']) . "\n";
    
    echo "\n";
}

// Test with BetParser which uses BettingSystem
echo "=== TESTING WITH BETPARSER ===\n\n";
foreach ($inputs as $label => $input) {
    echo "=== $label ===\n";
    
    $parser = new BetParser();
    $result = $parser->parse($input);
    
    echo "Valid: " . ($result['valid'] ? 'Yes' : 'No') . "\n";
    echo "Total: " . $result['total'] . "\n";
    echo "Items: " . count($result['items']) . "\n\n";
    
    foreach ($result['items'] as $index => $item) {
        echo "Item " . ($index + 1) . ":\n";
        echo "  Type: " . $item['type'] . "\n";
        echo "  Number: " . $item['number'] . "\n";
        echo "  Bet Type: " . $item['bet_type'] . "\n";
        echo "  Lottery Types: " . implode(", ", $item['lottery_type']) . "\n";
        echo "  Periods: " . $item['periods'] . "\n";
        echo "  Amount: " . $item['amount'] . "\n";
        echo "  Description: " . $item['description'] . "\n\n";
    }
    
    echo "\n";
} 