<?php
/**
 * Transaction Helper Functions
 * 
 * Helper functions for formatting and calculating transaction data
 */

/**
 * Get CSS class for transaction row based on transaction type
 * 
 * @param string $type Transaction type
 * @return string CSS class name
 */
function getTransactionClass($type) {
    switch ($type) {
        case 'deposit':
            return 'table-success';
        case 'withdraw':
            return 'table-danger';
        case 'commission':
            return 'table-primary';
        case 'refund':
            return 'table-warning';
        default:
            return '';
    }
}

/**
 * Get formatted transaction type name with badge
 * 
 * @param string $type Transaction type
 * @return string HTML for transaction type badge
 */
function getTransactionTypeName($type) {
    switch ($type) {
        case 'deposit':
            return '<span class="badge bg-success">充值</span>';
        case 'withdraw':
            return '<span class="badge bg-danger">下注</span>';
        case 'commission':
            return '<span class="badge bg-primary">佣金</span>';
        case 'refund':
            return '<span class="badge bg-warning text-dark">退款</span>';
        default:
            return '<span class="badge bg-secondary">其他</span>';
    }
}

/**
 * Format amount with sign based on transaction type
 * 
 * @param float $amount Transaction amount
 * @param string $type Transaction type
 * @return string Formatted amount with sign and styling
 */
function getAmountWithSign($amount, $type) {
    $formatted = number_format($amount, 2);
    switch ($type) {
        case 'deposit':
        case 'commission':
        case 'refund':
            return '<span class="text-success">+' . $formatted . '</span>';
        case 'withdraw':
            return '<span class="text-danger">-' . $formatted . '</span>';
        default:
            return $formatted;
    }
}

/**
 * Calculate total amount for transactions of a specific type
 * 
 * @param array $transactions Array of transaction records
 * @param string $type Transaction type to calculate
 * @return string Formatted total amount
 */
function calculateTotalByType($transactions, $type) {
    $total = 0;
    foreach ($transactions as $transaction) {
        if ($transaction['type'] === $type) {
            $total += $transaction['amount'];
        }
    }
    return number_format($total, 2);
}

/**
 * Helper functions for transaction-related operations
 */

/**
 * Format transaction amount with currency symbol and decimal places
 * 
 * @param float $amount The amount to format
 * @param string $currency The currency symbol to use (default: ¥)
 * @param int $decimals Number of decimal places (default: 2)
 * @return string Formatted amount with currency symbol
 */
function formatTransactionAmount($amount, $currency = '¥', $decimals = 2) {
    return $currency . ' ' . number_format($amount, $decimals);
}

/**
 * Get CSS class for transaction type
 * 
 * @param string $type The transaction type
 * @return string CSS class name
 */
function getTransactionTypeClass($type) {
    $classes = [
        'deposit' => 'success',
        'withdraw' => 'danger',
        'commission' => 'info',
        'purchase' => 'warning',
        'refund' => 'primary',
        'adjustment' => 'secondary'
    ];
    
    return isset($classes[$type]) ? $classes[$type] : 'secondary';
}

/**
 * Format transaction type for display
 * 
 * @param string $type The transaction type
 * @return string Formatted type with badge
 */
function formatTransactionType($type) {
    $class = getTransactionTypeClass($type);
    return "<span class=\"badge bg-{$class}\">" . ucfirst($type) . "</span>";
}

/**
 * Calculate transaction statistics
 * 
 * @param array $transactions Array of transaction records
 * @return array Statistics including total amount, counts by type, etc.
 */
function calculateTransactionStats($transactions) {
    $stats = [
        'total_amount' => 0,
        'total_count' => count($transactions),
        'by_type' => [],
        'by_day' => []
    ];
    
    foreach ($transactions as $transaction) {
        $stats['total_amount'] += $transaction['amount'];
        
        // Count by type
        if (!isset($stats['by_type'][$transaction['type']])) {
            $stats['by_type'][$transaction['type']] = [
                'count' => 0,
                'amount' => 0
            ];
        }
        
        $stats['by_type'][$transaction['type']]['count']++;
        $stats['by_type'][$transaction['type']]['amount'] += $transaction['amount'];
        
        // Group by day
        $day = date('Y-m-d', strtotime($transaction['created_at']));
        if (!isset($stats['by_day'][$day])) {
            $stats['by_day'][$day] = [
                'count' => 0,
                'amount' => 0
            ];
        }
        
        $stats['by_day'][$day]['count']++;
        $stats['by_day'][$day]['amount'] += $transaction['amount'];
    }
    
    return $stats;
}

/**
 * Generate data for transaction chart
 * 
 * @param array $transactions Array of transaction records
 * @return array Chart data including labels and datasets
 */
function generateTransactionChartData($transactions) {
    $stats = calculateTransactionStats($transactions);
    
    $labels = array_keys($stats['by_type']);
    $data = array_map(function($type) use ($stats) {
        return $stats['by_type'][$type]['amount'];
    }, $labels);
    
    $backgroundColor = [
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];
    
    return [
        'labels' => array_map('ucfirst', $labels),
        'datasets' => [
            [
                'data' => $data,
                'backgroundColor' => array_slice($backgroundColor, 0, count($labels))
            ]
        ]
    ];
} 