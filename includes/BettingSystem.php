<?php
/**
 * BettingSystem Class
 * 
 * Handles parsing and processing of betting inputs with support for various formats
 */
class BettingSystem {
    private $input;
    private $periods = 1; // Default to 1 period (D or D1)
    private $lotteryTypes = [];
    private $bets = [];
    private $totalAmount = 0;

    /**
     * Constructor
     * @param string $input The betting input string
     */
    public function __construct($input) {
        $this->input = $input;
        $this->parseInput();
    }

    /**
     * Parse the input string into components
     */
    private function parseInput() {
        // Split the input into lines
        $lines = explode("\n", trim($this->input));
        
        // Process each line
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
            
            // Check for period (D, D1, D2, etc.)
            if (preg_match('/^D(\d*)$/', $line, $matches)) {
                $this->periods = empty($matches[1]) ? 1 : intval($matches[1]);
                continue;
            }
            
            // Check for lottery type (#1, #2, etc.)
            if (preg_match('/^#([1-7]+)$/', $line, $matches)) {
                $this->parseLotteryType($matches[1]);
                continue;
            }
            
            // If line starts with a number, assume it's a bet
            if (preg_match('/^\d+/', $line)) {
                $this->parseBet($line);
            }
        }
        
        // Calculate the total amount
        $this->calculateTotalAmount();
    }

    /**
     * Parse lottery type
     * @param string $typeStr String of numbers representing lottery types
     */
    private function parseLotteryType($typeStr) {
        $types = str_split($typeStr);
        $typeMap = [
            '1' => 'M',
            '2' => 'P',
            '3' => 'T',
            '4' => 'S',
            '5' => 'B',
            '6' => 'K',
            '7' => 'W'
        ];
        
        foreach ($types as $type) {
            if (isset($typeMap[$type])) {
                $this->lotteryTypes[] = $typeMap[$type];
            }
        }
        
        // If no lottery types are specified, default to all types
        if (empty($this->lotteryTypes)) {
            $this->lotteryTypes = array_values($typeMap);
        }
    }

    /**
     * Parse bet line
     * @param string $line Bet line like "1234#1#1#1#1#1"
     */
    private function parseBet($line) {
        // Split by '#'
        $parts = explode('#', $line);
        
        // First part is the number
        $number = $parts[0];
        
        // Check if number is valid (4 digits)
        if (!preg_match('/^\d{4}$/', $number)) {
            // Handle error or skip
            return;
        }
        
        // Create bet structure
        $bet = [
            'number' => $number,
            'B' => 0, // 1st #
            'S' => 0, // 2nd #
            '4A' => 0, // 3rd #
            '4B' => 0, // 4th #
            '4C' => 0  // 5th #
        ];
        
        // Map positions to bet types
        $positionMap = [1 => 'B', 2 => 'S', 3 => '4A', 4 => '4B', 5 => '4C'];
        
        // Process amounts
        for ($i = 1; $i < count($parts); $i++) {
            if (!empty($parts[$i]) && is_numeric($parts[$i])) {
                $betType = $positionMap[$i] ?? null;
                if ($betType) {
                    $bet[$betType] = floatval($parts[$i]);
                }
            }
        }
        
        $this->bets[] = $bet;
    }

    /**
     * Calculate the total amount
     */
    private function calculateTotalAmount() {
        foreach ($this->bets as $bet) {
            $betAmount = $bet['B'] + $bet['S'] + $bet['4A'] + $bet['4B'] + $bet['4C'];
            $this->totalAmount += $betAmount * count($this->lotteryTypes) * $this->periods;
        }
    }

    /**
     * Format and display the parsed bet
     * @return string Formatted betting information
     */
    public function formatBet() {
        $output = "";
        
        // Display period
        $output .= "Period: D" . ($this->periods > 1 ? $this->periods : "") . "\n";
        
        // Display lottery types
        $output .= "Lottery Types: " . implode(", ", $this->lotteryTypes) . "\n";
        
        // Display bets
        $output .= "Bets:\n";
        foreach ($this->bets as $bet) {
            $output .= "  Number: " . $bet['number'] . "\n";
            if ($bet['B'] > 0) $output .= "    Big (B): " . $bet['B'] . "\n";
            if ($bet['S'] > 0) $output .= "    Small (S): " . $bet['S'] . "\n";
            if ($bet['4A'] > 0) $output .= "    4A: " . $bet['4A'] . "\n";
            if ($bet['4B'] > 0) $output .= "    4B: " . $bet['4B'] . "\n";
            if ($bet['4C'] > 0) $output .= "    4C: " . $bet['4C'] . "\n";
        }
        
        // Display total amount
        $output .= "Total Amount: " . number_format($this->totalAmount, 2) . "\n";
        
        return $output;
    }

    /**
     * Get the total amount
     * @return float Total betting amount
     */
    public function getTotalAmount() {
        return $this->totalAmount;
    }
    
    /**
     * Get all parsed data as array
     * @return array All betting data
     */
    public function getBettingData() {
        return [
            'periods' => $this->periods,
            'lotteryTypes' => $this->lotteryTypes,
            'bets' => $this->bets,
            'totalAmount' => $this->totalAmount
        ];
    }
} 