<?php
class TransactionModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // 获取所有交易
    public function getAllTransactions() {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username 
            FROM transactions t 
            JOIN users u ON t.user_id = u.id 
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取用户交易记录
    public function getUserTransactions($user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取特定类型的交易记录
    public function getTransactionsByType($type, $user_id = null) {
        $sql = "SELECT * FROM transactions WHERE type = ?";
        $params = [$type];
        
        if ($user_id !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $user_id;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 创建新交易记录
    public function createTransaction($data) {
        $stmt = $this->db->prepare("
            INSERT INTO transactions (user_id, order_id, type, amount, balance_before, balance_after, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['user_id'],
            $data['order_id'] ?? null,
            $data['type'],
            $data['amount'],
            $data['balance_before'],
            $data['balance_after'],
            $data['notes'] ?? null
        ]);
    }
    
    // 获取特定时间段内的交易统计
    public function getTransactionStats($start_date, $end_date, $type = null) {
        $sql = "
            SELECT 
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count
            FROM transactions 
            WHERE created_at BETWEEN ? AND ?
        ";
        
        $params = [$start_date, $end_date];
        
        if ($type !== null) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 获取佣金统计
    public function getCommissionStats($user_id = null, $start_date = null, $end_date = null) {
        $sql = "
            SELECT 
                SUM(amount) as total_commission
            FROM transactions 
            WHERE type = 'commission'
        ";
        
        $params = [];
        
        if ($user_id !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $user_id;
        }
        
        if ($start_date !== null && $end_date !== null) {
            $sql .= " AND created_at BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() ?: 0;
    }
    
    // 获取销售统计（减去佣金）
    public function getSalesStats($start_date = null, $end_date = null) {
        $sql = "
            SELECT 
                SUM(CASE WHEN type = 'withdraw' THEN amount ELSE 0 END) as total_sales,
                SUM(CASE WHEN type = 'commission' THEN amount ELSE 0 END) as total_commission
            FROM transactions
        ";
        
        $params = [];
        
        if ($start_date !== null && $end_date !== null) {
            $sql .= " WHERE created_at BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_sales = $result['total_sales'] ?: 0;
        $total_commission = $result['total_commission'] ?: 0;
        
        return [
            'total_sales' => $total_sales,
            'total_commission' => $total_commission,
            'net_sales' => $total_sales - $total_commission
        ];
    }
    
    // 获取筛选后的交易记录
    public function getFilteredTransactions($filters = []) {
        $sql = "
            SELECT t.*, u.username 
            FROM transactions t 
            JOIN users u ON t.user_id = u.id 
            WHERE 1=1
        ";
        $params = [];
        
        // 日期范围筛选
        if (isset($filters['date_range']) && is_array($filters['date_range']) && count($filters['date_range']) === 2) {
            $sql .= " AND t.created_at BETWEEN ? AND ?";
            $params[] = $filters['date_range'][0];
            $params[] = $filters['date_range'][1];
        }
        
        // 用户ID筛选
        if (isset($filters['user_id'])) {
            $sql .= " AND t.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        // 交易类型筛选
        if (isset($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }
        
        // 最小金额筛选
        if (isset($filters['min_amount'])) {
            $sql .= " AND t.amount >= ?";
            $params[] = $filters['min_amount'];
        }
        
        // 排序
        $sql .= " ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 