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
            $this->db->beginTransaction();
            
            // 生成订单号
            $order_number = Utility::generateOrderNumber();
            
            // 创建订单
            $stmt = $this->db->prepare("
                INSERT INTO orders (order_number, user_id, content, total_amount, lottery_types, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->execute([
                $order_number,
                $data['user_id'],
                $data['content'],
                $data['total_amount'],
                $data['lottery_types'] ?? 'MPTS'
            ]);
            
            $order_id = $this->db->lastInsertId();
            
            // 更新用户余额
            $userModel = new UserModel();
            $new_balance = $userModel->updateBalance($data['user_id'], $data['total_amount'], 'subtract');
            
            if ($new_balance === false) {
                $this->db->rollBack();
                return false;
            }
            
            // 创建交易记录
            $stmt = $this->db->prepare("
                INSERT INTO transactions (user_id, order_id, type, amount, balance_before, balance_after, notes) 
                VALUES (?, ?, 'withdraw', ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['user_id'],
                $order_id,
                $data['total_amount'],
                $new_balance + $data['total_amount'],
                $new_balance,
                '订单号: ' . $order_number
            ]);
            
            $this->db->commit();
            return $order_id;
        } catch (Exception $e) {
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
    public function calculateCommission($user_id, $amount) {
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
            
            // 更新上级代理余额
            $new_balance = $userModel->updateBalance($parent['id'], $commission_amount, 'add');
            
            if ($new_balance === false) {
                return false;
            }
            
            // 创建交易记录
            $stmt = $this->db->prepare("
                INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, notes) 
                VALUES (?, 'commission', ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $parent['id'],
                $commission_amount,
                $new_balance - $commission_amount,
                $new_balance,
                '下级代理 ' . $user['username'] . ' 的佣金'
            ]);
            
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
} 