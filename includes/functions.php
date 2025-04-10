<?php
/**
 * Common functions used across the application
 */

/**
 * Get the current authenticated user
 * 
 * @return object|null User object if authenticated, null otherwise
 */
function auth() {
    static $auth = null;
    
    if ($auth === null) {
        $auth = new class {
            public function user() {
                if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
                    return null;
                }
                
                $userId = $_SESSION['user']['id'];
                $userModel = new UserModel();
                $userData = $userModel->getUserById($userId);
                
                if (is_object($userData)) {
                    $userData = (array)$userData;
                }
                
                return $userData;
            }
            
            public function check() {
                return isset($_SESSION['user']) && !empty($_SESSION['user']);
            }
            
            public function id() {
                return $_SESSION['user']['id'] ?? null;
            }
        };
    }
    
    return $auth;
}

/**
 * Format a number for display as currency
 * 
 * @param float $number The number to format
 * @param int $decimals Number of decimal places
 * @return string Formatted number
 */
function format_number($number, $decimals = 2) {
    return number_format($number, $decimals);
}

/**
 * Format a date for display
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Check if a user has the required role
 * 
 * @param string|array $roles Required role(s)
 * @return bool Whether user has the required role
 */
function has_role($roles) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
        return false;
    }
    
    $user_role = $_SESSION['user']['role'];
    
    if (is_array($roles)) {
        return in_array($user_role, $roles);
    }
    
    return $user_role === $roles;
} 