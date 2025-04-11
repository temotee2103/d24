<?php
class Database {
    private static $instance = null;
    private $conn;
    private $stmt;
    
    public function __construct() {
        error_log("DEBUG: Database __construct started."); // Checkpoint DB1
        $config = require_once __DIR__ . '/../config/database.php';
        error_log("DEBUG: Database config loaded: " . print_r($config, true)); // Checkpoint DB2
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
            error_log("DEBUG: PDO DSN: " . $dsn); // Checkpoint DB3
            $this->conn = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            error_log("DEBUG: PDO connection successful."); // Checkpoint DB4

            // SET TIME ZONE for the connection
            $this->conn->exec("SET time_zone = '+8:00'"); // Set to GMT+8 for this session
            error_log("DEBUG: PDO connection time_zone set to +8:00."); 

        } catch (PDOException $e) {
            error_log("FATAL: PDO Connection Error in Database __construct: " . $e->getMessage()); // Log fatal error
            // We should probably re-throw or handle more gracefully than die
            throw $e; // Re-throw exception
            // die("数据库连接失败: " . $e->getMessage()); 
        }
        error_log("DEBUG: Database __construct finished."); // Checkpoint DB5
    }
    
    public static function getInstance() {
        error_log("DEBUG: Database getInstance called."); // Checkpoint DB6
        if (self::$instance === null) {
            error_log("DEBUG: Database instance is null, creating new instance."); // Checkpoint DB7
            try {
                self::$instance = new self();
                 error_log("DEBUG: Database new instance created successfully."); // Checkpoint DB8
            } catch (Exception $e) {
                 error_log("FATAL: Exception during new self() in getInstance: " . $e->getMessage()); // Log fatal error
                 self::$instance = null; // Ensure instance remains null on failure
                 throw $e; // Re-throw
            }
        } else {
             error_log("DEBUG: Database instance already exists, returning existing."); // Checkpoint DB9
        }
        error_log("DEBUG: Database getInstance returning instance. Is it null? " . (self::$instance === null ? 'YES' : 'NO')); // Checkpoint DB10
        return self::$instance;
    }
    
    public function query($sql) {
        $this->stmt = $this->conn->prepare($sql);
        return $this;
    }
    
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }
    
    public function execute() {
        return $this->stmt->execute();
    }
    
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    public function prepare($query) {
        return $this->conn->prepare($query);
    }
    
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollBack() {
        return $this->conn->rollBack();
    }
    
    /**
     * 获取数据库连接对象
     */
    public function getConnection() {
        return $this->conn;
    }
} 