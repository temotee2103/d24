<?php
/**
 * BetParser Test Class 
 * 
 * This class is meant to test the integration of BettingSystem and BetParser with the application.
 */
class BetParserTest {
    /**
     * Run a test to verify the BettingSystem and BetParser integration
     * 
     * @return array Test results
     */
    public static function runTest() {
        // Create test data
        $inputs = [
            "Standard Format" => "D\n#1234\n1234#1#1#1#1#1",
            "Multi-Period" => "D2\n#1\n1234#1#1",
            "With Gaps" => "D3\n#12\n1234##1##1"
        ];
        
        $results = [];
        
        foreach ($inputs as $label => $input) {
            // Parse using BettingSystem
            try {
                $bettingSystem = new BettingSystem($input);
                $directData = $bettingSystem->getBettingData();
                
                // Parse using BetParser
                $parser = new BetParser();
                $parsedData = $parser->parse($input);
                
                $results[$label] = [
                    'input' => $input,
                    'bettingSystem' => [
                        'success' => true,
                        'periods' => $directData['periods'],
                        'lotteryTypes' => $directData['lotteryTypes'],
                        'bets' => count($directData['bets']),
                        'totalAmount' => $directData['totalAmount'],
                    ],
                    'betParser' => [
                        'success' => $parsedData['valid'] ?? false,
                        'total' => $parsedData['total'] ?? 0,
                        'items' => count($parsedData['items'] ?? [])
                    ]
                ];
            } catch (Exception $e) {
                $results[$label] = [
                    'input' => $input,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
} 