<?php
class OrderModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // 获取所有订单
    public function getAllOrders() {
        $stmt = $this->db->prepare("
            SELECT o.*, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取用户订单
    public function getUserOrders($user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM orders 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 通过ID获取订单
    public function getOrderById($id) {
        $stmt = $this->db->prepare("
            SELECT o.*, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 创建新订单
    public function createOrder($data) {
        try {
            Logger::log('Starting to create order in OrderModel', $data);
            
            $this->db->beginTransaction();
            
            // 生成订单号
            $order_number = Utility::generateOrderNumber();
            Logger::log('Generated order number', ['order_number' => $order_number]);
            
            // 创建订单
            $stmt = $this->db->prepare("
                INSERT INTO orders (order_number, user_id, content, total_amount, lottery_types, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            
            $result = $stmt->execute([
                $order_number,
                $data['user_id'],
                $data['content'],
                $data['total_amount'],
                $data['lottery_types'] ?? 'MPTS'
            ]);
            
            if (!$result) {
                Logger::log('Failed to insert order into database', ['error' => $stmt->errorInfo()]);
                $this->db->rollBack();
                return false;
            }
            
            $order_id = $this->db->lastInsertId();
            Logger::log('Order inserted', ['order_id' => $order_id]);
            
            // 更新用户余额
            $userModel = new UserModel();
            $new_balance = $userModel->updateBalance($data['user_id'], $data['total_amount'], 'subtract');
            
            if ($new_balance === false) {
                Logger::log('Failed to update user balance', ['user_id' => $data['user_id'], 'amount' => $data['total_amount']]);
                $this->db->rollBack();
                return false;
            }
            
            Logger::log('User balance updated', ['new_balance' => $new_balance]);
            
            // 创建交易记录
            $stmt = $this->db->prepare("
                INSERT INTO transactions (user_id, order_id, type, amount, balance_before, balance_after, notes) 
                VALUES (?, ?, 'withdraw', ?, ?, ?, ?)
            ");
            
            $transResult = $stmt->execute([
                $data['user_id'],
                $order_id,
                $data['total_amount'],
                $new_balance + $data['total_amount'],
                $new_balance,
                '订单号: ' . $order_number
            ]);
            
            if (!$transResult) {
                Logger::log('Failed to create transaction record', ['error' => $stmt->errorInfo()]);
                $this->db->rollBack();
                return false;
            }
            
            Logger::log('Transaction record created');
            
            // 计算佣金
            $commResult = $this->calculateCommission($data['user_id'], $order_id, $data['total_amount']);
            
            if ($commResult === false) {
                Logger::log('Failed to calculate commission');
                $this->db->rollBack();
                return false;
            }
            
            Logger::log('Commission calculated successfully');
            
            // 提交事务
            $this->db->commit();
            Logger::log('Order created successfully', ['order_id' => $order_id]);
            
            return $order_id;
        } catch (Exception $e) {
            Logger::log('Exception in createOrder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->db->rollBack();
            return false;
        }
    }
    
    // 更新订单状态
    public function updateOrderStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    // 取消订单
    public function cancelOrder($id) {
        try {
            $this->db->beginTransaction();
            
            // 获取订单信息
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order || $order['status'] !== 'pending') {
                $this->db->rollBack();
                return false;
            }
            
            // 更新订单状态
            $stmt = $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if (!$result) {
                $this->db->rollBack();
                return false;
            }
            
            // 归还用户余额
            $userModel = new UserModel();
            $new_balance = $userModel->updateBalance($order['user_id'], $order['total_amount'], 'add');
            
            if ($new_balance === false) {
                $this->db->rollBack();
                return false;
            }
            
            // 创建交易记录
            $stmt = $this->db->prepare("
                INSERT INTO transactions (user_id, order_id, type, amount, balance_before, balance_after, notes) 
                VALUES (?, ?, 'deposit', ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $order['user_id'],
                $id,
                $order['total_amount'],
                $new_balance - $order['total_amount'],
                $new_balance,
                '订单取消退款: ' . $order['order_number']
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // 计算佣金
    public function calculateCommission($user_id, $order_id, $amount) {
        try {
            $userModel = new UserModel();
            $user = $userModel->getUserById($user_id);
            
            if (!$user || $user['parent_id'] === null) {
                return true; // 如果用户没有上级代理，直接返回成功
            }
            
            // 获取当前用户的佣金比例
            $user_commission_rate = $user['commission_rate'];
            
            // 获取上级代理信息
            $parent = $userModel->getUserById($user['parent_id']);
            
            if (!$parent) {
                return true; // 如果上级代理不存在，直接返回成功
            }
            
            // 计算上级代理应得的佣金
            $parent_commission_rate = $parent['commission_rate'];
            $commission_amount = $amount * ($parent_commission_rate - $user_commission_rate) / 100;
            
            if ($commission_amount <= 0) {
                return true; // 如果没有佣金，直接返回成功
            }
            
            /* // -- REMOVED: Do not add commission directly to user balance --
            // 更新上级代理余额
            $new_balance = $userModel->updateBalance($parent['id'], $commission_amount, 'add');
            
            if ($new_balance === false) {
                Logger::log('Error updating parent balance for commission', ['parent_id' => $parent['id'], 'amount' => $commission_amount]);
                // return false; 
            }
            */ // -- END REMOVED --
            
            // 创建交易记录 (Re-enabled, but reflects NO balance change)
            // This transaction serves reporting purposes for financial details
            $stmt = $this->db->prepare("
                INSERT INTO transactions (user_id, order_id, type, amount, balance_before, balance_after, notes) 
                VALUES (?, ?, 'commission', ?, ?, ?, ?)
            ");
            
            // Get the parent's current balance to record (balance doesn't change due to this tx)
            $current_balance = $parent['balance']; // Assuming $parent array has the up-to-date balance
            
            $transResult = $stmt->execute([
                $parent['id'],
                $order_id,
                $commission_amount,
                $current_balance, // balance_before is the current balance
                $current_balance, // balance_after is THE SAME (balance doesn't change here)
                '佣金入账(未计入余额): 下级代理 ' . $user['username'] // Updated note
            ]);

            if (!$transResult) {
                 Logger::log('Failed to create commission transaction record', ['error' => $stmt->errorInfo(), 'parent_id' => $parent['id'], 'order_id' => $order_id]);
                 // Decide if this failure should cause rollback
                 // return false; 
            }
            
            // 创建佣金记录 (This part remains)
            $commissionModel = new CommissionModel();
            $addCommResult = $commissionModel->addCommission(
                $parent['id'], 
                $commission_amount, 
                $order_id, 
                '来自订单的佣金 - 下级代理: ' . $user['username']
            );

            if (!$addCommResult) {
                Logger::log('Failed to add commission record', ['parent_id' => $parent['id'], 'amount' => $commission_amount, 'order_id' => $order_id]);
                // Depending on requirements, failure to add commission record might need to rollback the order.
                // return false; // <-- Decide if this failure should cause rollback
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // 删除订单（仅用于测试，实际应用中应该只是标记为已删除而不是真正删除）
    public function deleteOrder($id) {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 获取指定日期范围内的所有订单
     */
    public function getOrdersByDateRange($startDate, $endDate) {
        $db = Database::getInstance();
        
        $query = "SELECT o.*, u.username 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE DATE(o.created_at) BETWEEN :start_date AND :end_date
                 ORDER BY o.created_at DESC";
                 
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取指定代理的所有下线用户 (包括间接下线) 的 ID 列表
     */
    private function getSubagentIdsRecursive($agentId) {
        $userModel = new UserModel(); // Need UserModel instance
        $subagentIds = [];
        $directSubagents = $userModel->getSubagents($agentId);
        
        foreach ($directSubagents as $subagent) {
            $subagentIds[] = $subagent['id'];
            // Recursively get subagents of the subagent
            $subagentIds = array_merge($subagentIds, $this->getSubagentIdsRecursive($subagent['id']));
        }
        return $subagentIds;
    }

    /**
     * 获取指定代理的所有下线 (包括间接下线) 在指定日期范围内的订单
     */
    public function getSubagentOrdersByDateRange($agentId, $startDate, $endDate) {
        $subagentIds = $this->getSubagentIdsRecursive($agentId);
        
        if (empty($subagentIds)) {
            return []; // No subagents, no orders
        }
        
        $db = Database::getInstance();
        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($subagentIds), '?'));
        
        $query = "SELECT o.*, u.username 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE o.user_id IN ({$placeholders}) 
                 AND DATE(o.created_at) BETWEEN ? AND ?
                 ORDER BY o.created_at DESC";
                 
        $params = array_merge($subagentIds, [$startDate, $endDate]);
                 
        $stmt = $db->getConnection()->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取指定用户在指定日期范围内的所有订单
     */
    public function getUserOrdersByDateRange($userId, $startDate, $endDate) {
        $db = Database::getInstance();
        
        $query = "SELECT o.*, u.username 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 WHERE o.user_id = :user_id AND DATE(o.created_at) BETWEEN :start_date AND :end_date
                 ORDER BY o.created_at DESC";
                 
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 