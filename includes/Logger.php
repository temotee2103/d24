<?php
/**
 * Simple Logger class
 */
class Logger {
    private static $debugMode = false;
    private static $logFile = 'debug.log';
    private static $initialized = false;
    
    /**
     * Initialize the logger
     * 
     * @param string $logFile Path to the log file
     * @return void
     */
    public static function init($logFile = null) {
        if ($logFile !== null) {
            self::$logFile = $logFile;
        } else {
            self::$logFile = ROOT_PATH . '/logs/debug.log';
        }
        
        // Create log directory if it doesn't exist
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        self::$initialized = true;
    }
    
    /**
     * Set debug mode
     * 
     * @param bool $mode Whether debug mode is enabled
     * @return void
     */
    public static function setDebugMode($mode = true) {
        self::$debugMode = $mode;
    }
    
    /**
     * Log a message
     * 
     * @param string $message The message to log
     * @param array $data Optional data to include in the log
     * @return void
     */
    public static function log($message, $data = []) {
        if (!self::$debugMode) {
            return;
        }
        
        if (!self::$initialized) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message";
        
        if (!empty($data)) {
            $logMessage .= "\n" . print_r($data, true);
        }
        
        $logMessage .= "\n-------------------------------------------\n";
        
        error_log($logMessage, 3, self::$logFile);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message The error message
     * @param array $data Optional data to include in the log
     * @return void
     */
    public static function error($message, $data = []) {
        if (!self::$initialized) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] ERROR: $message";
        
        if (!empty($data)) {
            $logMessage .= "\n" . print_r($data, true);
        }
        
        $logMessage .= "\n-------------------------------------------\n";
        
        error_log($logMessage, 3, self::$logFile);
    }
} 