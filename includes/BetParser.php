<?php
/**
 * BetParser Class
 * 
 * Handles parsing and processing of betting inputs using the BettingSystem class
 */

class BetParser {
    /**
     * Parse betting input string
     * 
     * @param string $input The betting input string
     * @return array The parsed betting data
     */
    public function parse($input) {
        try {
            // Create a new BettingSystem instance with the input
            $bettingSystem = new BettingSystem($input);
            
            // Get the betting data from the system
            $bettingData = $bettingSystem->getBettingData();
            
            // Process the data into a format expected by the application
            $result = $this->processData($bettingData);
            
            // Return the formatted result
            return $result;
        } catch (Exception $e) {
            // Return error message if parsing fails
            return [
                'valid' => false,
                'message' => 'Parse error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process the betting data into application format
     * 
     * @param array $bettingData The data from BettingSystem
     * @return array Formatted data for application use
     */
    private function processData($bettingData) {
        // Extract information from betting data
        $periods = $bettingData['periods'];
        $lotteryTypes = $bettingData['lotteryTypes'];
        $bets = $bettingData['bets'];
        $totalAmount = $bettingData['totalAmount'];
        
        // Create result structure
        $result = [
            'valid' => true,
            'total' => $totalAmount,
            'items' => []
        ];
        
        // Map lottery types to their codes
        $lotteryTypeMap = [
            'M' => '1', // 万能
            'P' => '2', // 四合彩 
            'T' => '3', // 多多
            'S' => '4', // 新加坡
            'B' => '5', // 沙巴
            'K' => '6', // 山打根
            'W' => '7'  // 砂拉越
        ];
        
        // Format lottery types string for database (numeric)
        $lotteryTypeString = implode('', array_map(function($type) use ($lotteryTypeMap) {
            return $lotteryTypeMap[$type] ?? '';
        }, $lotteryTypes));
        $result['lottery_types_numeric'] = $lotteryTypeString;
        // Also add the original letters array
        $result['lottery_types_letters'] = $lotteryTypes;
        
        // Process each bet
        foreach ($bets as $bet) {
            $number = $bet['number'];
            
            // Process Big (B) bet
            if ($bet['B'] > 0) {
                $this->addBetItem($result['items'], $number, 'B', $bet['B'], $periods, $lotteryTypes, $lotteryTypeMap);
            }
            
            // Process Small (S) bet
            if ($bet['S'] > 0) {
                $this->addBetItem($result['items'], $number, 'S', $bet['S'], $periods, $lotteryTypes, $lotteryTypeMap);
            }
            
            // Process 4A bet
            if ($bet['4A'] > 0) {
                $this->addBetItem($result['items'], $number, '4A', $bet['4A'], $periods, $lotteryTypes, $lotteryTypeMap);
            }
            
            // Process 4B bet
            if ($bet['4B'] > 0) {
                $this->addBetItem($result['items'], $number, '4B', $bet['4B'], $periods, $lotteryTypes, $lotteryTypeMap);
            }
            
            // Process 4C bet
            if ($bet['4C'] > 0) {
                $this->addBetItem($result['items'], $number, '4C', $bet['4C'], $periods, $lotteryTypes, $lotteryTypeMap);
            }
        }
        
        return $result;
    }
    
    /**
     * Add a bet item to the result items array
     * 
     * @param array &$items Reference to items array
     * @param string $number Bet number
     * @param string $betType Type of bet (B, S, 4A, etc.)
     * @param float $amount Bet amount
     * @param int $periods Number of periods
     * @param array $lotteryTypes Array of lottery types
     * @param array $lotteryTypeMap Map of lottery types to their codes
     */
    private function addBetItem(&$items, $number, $betType, $amount, $periods, $lotteryTypes, $lotteryTypeMap) {
        // Create formatted lottery type codes
        $formattedLotteryTypes = [];
        foreach ($lotteryTypes as $type) {
            if (isset($lotteryTypeMap[$type])) {
                $formattedLotteryTypes[] = $lotteryTypeMap[$type];
            }
        }
        
        // Create bet type name based on type
        $betTypeName = '';
        switch ($betType) {
            case 'B':
                $betTypeName = '大';
                break;
            case 'S':
                $betTypeName = '小';
                break;
            case '4A':
                $betTypeName = '4A';
                break;
            case '4B':
                $betTypeName = '4B';
                break;
            case '4C':
                $betTypeName = '4C';
                break;
        }
        
        // Create description for the bet
        $description = $number . ' - ' . $betTypeName;
        if ($periods > 1) {
            $description .= ' (D' . $periods . ')';
        }
        
        // Calculate item amount (bet amount * lottery types * periods)
        $itemAmount = $amount * count($lotteryTypes) * $periods;
        
        // Add the item to the items array
        $items[] = [
            'type' => 'bet',
            'number' => $number,
            'bet_type' => $betTypeName,
            'bet_type_code' => $betType,
            'lottery_type' => $formattedLotteryTypes,
            'periods' => $periods,
            'amount' => $itemAmount,
            'description' => $description,
            'original_amount' => $amount
        ];
    }
} 