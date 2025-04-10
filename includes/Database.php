<?php
class Database {
    private static $instance = null;
    private $conn;
    private $stmt;
    
    public function __construct() {
        $config = require_once __DIR__ . '/../config/database.php';
        try {
            $this->conn = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
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