<?php

class CommissionModel {

    /**
     * 获取用户的佣金记录
     */
    public function getUserCommissions($userId, $limit = null) {
        $db = Database::getInstance();
        
        $query = "SELECT * FROM commissions WHERE user_id = ? ORDER BY created_at DESC";
        if ($limit) {
            $query .= " LIMIT " . intval($limit);
        }
        
        $stmt = $db->getConnection()->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取指定日期范围内的所有佣金记录
     */
    public function getCommissionsByDateRange($startDate, $endDate) {
        $db = Database::getInstance();
        
        $query = "SELECT c.*, u.username 
                 FROM commissions c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE DATE(c.created_at) BETWEEN :start_date AND :end_date
                 ORDER BY c.created_at DESC";
                 
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 添加佣金记录
     */
    public function addCommission($userId, $amount, $orderId, $note = '') {
        $db = Database::getInstance();
        
        $query = "INSERT INTO commissions (user_id, amount, order_id, note, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $db->getConnection()->prepare($query);
        return $stmt->execute([$userId, $amount, $orderId, $note]);
    }
    
    /**
     * 计算用户佣金总额
     */
    public function getTotalCommission($userId = null) {
        $db = Database::getInstance();
        
        if ($userId) {
            $query = "SELECT SUM(amount) as total FROM commissions WHERE user_id = ?";
            $stmt = $db->getConnection()->prepare($query);
            $stmt->execute([$userId]);
        } else {
            $query = "SELECT SUM(amount) as total FROM commissions";
            $stmt = $db->getConnection()->prepare($query);
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? floatval($result['total']) : 0;
    }
    
    /**
     * 获取指定时间段内的佣金统计
     */
    public function getCommissionStats($startDate, $endDate, $userId = null) {
        $db = Database::getInstance();
        
        $params = [$startDate, $endDate];
        $userCondition = '';
        
        if ($userId) {
            $userCondition = " AND user_id = ?";
            $params[] = $userId;
        }
        
        $query = "SELECT SUM(amount) as total 
                  FROM commissions 
                  WHERE DATE(created_at) BETWEEN ? AND ?{$userCondition}";
        
        $stmt = $db->getConnection()->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? floatval($result['total']) : 0;
    }
} 