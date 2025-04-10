<?php
/**
 * DB class
 * 
 * A simple wrapper around the Database class for backward compatibility
 */
class DB {
    private static $instance = null;
    
    /**
     * Get a database instance
     * 
     * @return Database Database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = Database::getInstance();
        }
        
        return self::$instance;
    }
    
    /**
     * Execute a query
     * 
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @return mixed Query result
     */
    public static function query($query, $params = []) {
        $db = self::getInstance();
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
}

// If the Database class doesn't exist, include it
if (!class_exists('Database')) {
    require_once __DIR__ . '/Database.php';
} 