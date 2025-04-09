<?php
class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // 通过ID获取用户
    public function getUserById($user_id) {
        if (empty($user_id)) {
            return false;
        }
        $this->db->query("SELECT * FROM users WHERE id = :id");
        $this->db->bind(':id', $user_id);
        return $this->db->single();
    }
    
    // 获取所有用户
    public function getAllUsers() {
        $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        return $this->db->resultSet();
    }
    
    // 获取用户总数
    public function getTotalUsers() {
        $this->db->query("SELECT COUNT(*) as total FROM users");
        $result = $this->db->single();
        return $result['total'];
    }
    
    // 获取指定日期范围内活跃的用户数量（有登录记录的用户）
    public function getActiveUsers($start_date, $end_date) {
        $this->db->query("
            SELECT COUNT(DISTINCT user_id) as active_users 
            FROM user_logs 
            WHERE action = 'login' AND created_at BETWEEN :start_date AND :end_date
        ");
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        $result = $this->db->single();
        return $result['active_users'];
    }
    
    // 获取指定日期新注册的用户数量
    public function getNewUsers($date) {
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';
        
        $this->db->query("
            SELECT COUNT(*) as new_users 
            FROM users 
            WHERE created_at BETWEEN :start AND :end
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $result = $this->db->single();
        return $result['new_users'];
    }
    
    // 获取用户增长趋势数据
    public function getUserGrowthData($days = 30) {
        $data = [];
        $currentDate = date('Y-m-d');
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days", strtotime($currentDate)));
            $count = $this->getNewUsers($date);
            $data[] = [
                'date' => $date,
                'count' => $count
            ];
        }
        
        return $data;
    }
    
    // 获取用户类型分布
    public function getUserTypeDistribution() {
        $this->db->query("
            SELECT role, COUNT(*) as count 
            FROM users 
            GROUP BY role
        ");
        return $this->db->resultSet();
    }
    
    // 获取最活跃的用户排行
    public function getTopActiveUsers($start_date, $end_date, $limit = 10) {
        $this->db->query("
            SELECT u.id, u.username, u.email, u.role, COUNT(l.id) as login_count 
            FROM users u 
            JOIN user_logs l ON u.id = l.user_id 
            WHERE l.action = 'login' AND l.created_at BETWEEN :start_date AND :end_date 
            GROUP BY u.id 
            ORDER BY login_count DESC 
            LIMIT :limit
        ");
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    
    // 添加新用户
    public function addUser($data) {
        $this->db->query("
            INSERT INTO users (username, email, password, role, created_at) 
            VALUES (:username, :email, :password, :role, NOW())
        ");
        
        // 绑定参数
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role', $data['role'] ?? 'user');
        
        // 执行
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // 更新用户信息
    public function updateUser($id, $data) {
        // 准备SQL语句
        $sql = "UPDATE users SET ";
        $params = [];
        
        // 检查哪些字段需要更新
        if (isset($data['password']) && !empty($data['password'])) {
            $sql .= "password = :password, ";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['nickname'])) {
            $sql .= "nickname = :nickname, ";
            $params[':nickname'] = $data['nickname'];
        }
        
        if (isset($data['phone'])) {
            $sql .= "phone = :phone, ";
            $params[':phone'] = $data['phone'];
        }
        
        if (isset($data['notes'])) {
            $sql .= "notes = :notes, ";
            $params[':notes'] = $data['notes'];
        }
        
        if (isset($data['role'])) {
            $sql .= "role = :role, ";
            $params[':role'] = $data['role'];
        }
        
        if (isset($data['commission_rate'])) {
            $sql .= "commission_rate = :commission_rate, ";
            $params[':commission_rate'] = $data['commission_rate'];
        }
        
        if (isset($data['can_create_subagent'])) {
            $sql .= "can_create_subagent = :can_create_subagent, ";
            $params[':can_create_subagent'] = $data['can_create_subagent'];
        }
        
        if (isset($data['status'])) {
            $sql .= "status = :status, ";
            $params[':status'] = $data['status'];
        }
        
        if (isset($data['parent_id'])) {
            $sql .= "parent_id = :parent_id, ";
            $params[':parent_id'] = $data['parent_id'];
        }
        
        // 移除最后的逗号和空格
        $sql = rtrim($sql, ", ");
        
        // 添加WHERE条件
        $sql .= " WHERE id = :id";
        $params[':id'] = $id;
        
        // 执行更新
        $this->db->query($sql);
        
        // 绑定参数
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        // 执行
        return $this->db->execute();
    }
    
    // 删除用户
    public function deleteUser($id) {
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
    
    // 记录用户活动
    public function logUserActivity($user_id, $action, $details = null) {
        $this->db->query("
            INSERT INTO user_logs (user_id, action, details, created_at) 
            VALUES (:user_id, :action, :details, NOW())
        ");
        
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':action', $action);
        $this->db->bind(':details', $details);
        
        return $this->db->execute();
    }
    
    // 通过用户名获取用户
    public function getUserByUsername($username) {
        $this->db->query("SELECT * FROM users WHERE username = :username");
        $this->db->bind(':username', $username);
        return $this->db->single();
    }
    
    // 创建新用户
    public function createUser($data) {
        $this->db->query(
            "INSERT INTO users (username, password, nickname, phone, notes, role, commission_rate, can_create_subagent, parent_id) 
             VALUES (:username, :password, :nickname, :phone, :notes, :role, :commission_rate, :can_create_subagent, :parent_id)"
        );
        
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':nickname', $data['nickname']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':notes', $data['notes']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':commission_rate', $data['commission_rate']);
        $this->db->bind(':can_create_subagent', $data['can_create_subagent']);
        $this->db->bind(':parent_id', $data['parent_id']);
        
        return $this->db->execute();
    }
    
    // 获取下线用户
    public function getSubagents($parent_id) {
        $this->db->query("SELECT * FROM users WHERE parent_id = :parent_id");
        $this->db->bind(':parent_id', $parent_id);
        return $this->db->resultSet();
    }
    
    // 获取用户数量
    public function countSubagents($parent_id) {
        $this->db->query("SELECT COUNT(*) as count FROM users WHERE parent_id = :parent_id");
        $this->db->bind(':parent_id', $parent_id);
        $result = $this->db->single();
        return $result['count'] ?? 0;
    }
    
    // 验证用户密码
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
    
    // 更新用户余额
    public function updateBalance($id, $amount, $action = 'add') {
        try {
            // 获取当前用户信息
            $this->db->query("SELECT balance FROM users WHERE id = :id");
            $this->db->bind(':id', $id);
            $user = $this->db->single();
            
            if (!$user) {
                return false;
            }
            
            $balance = $user['balance'];
            $new_balance = $action === 'add' ? $balance + $amount : $balance - $amount;
            
            // 如果是减少余额，检查余额是否充足
            if ($action === 'subtract' && $new_balance < 0) {
                return false;
            }
            
            // 更新用户余额
            $this->db->query("UPDATE users SET balance = :balance WHERE id = :id");
            $this->db->bind(':balance', $new_balance);
            $this->db->bind(':id', $id);
            
            if ($this->db->execute()) {
                return $new_balance;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    // 将所有用户的信用额度转换为余额
    public function migrateAllCreditToBalance() {
        try {
            $this->db->beginTransaction();
            
            // 寻找所有有信用额度但余额为0的用户
            $this->db->query("SELECT id, credit_limit, balance FROM users WHERE credit_limit > 0 AND balance = 0");
            $users = $this->db->resultSet();
            
            $migrated_count = 0;
            
            foreach ($users as $user) {
                $this->db->query("UPDATE users SET balance = balance + :credit_limit WHERE id = :id");
                $this->db->bind(':credit_limit', $user['credit_limit']);
                $this->db->bind(':id', $user['id']);
                
                if ($this->db->execute()) {
                    $migrated_count++;
                    
                    // 记录转换日志
                    $this->db->query("
                        INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, notes) 
                        VALUES (:user_id, 'system', :amount, :balance_before, :balance_after, :notes)
                    ");
                    
                    $this->db->bind(':user_id', $user['id']);
                    $this->db->bind(':amount', $user['credit_limit']);
                    $this->db->bind(':balance_before', $user['balance']);
                    $this->db->bind(':balance_after', $user['balance'] + $user['credit_limit']);
                    $this->db->bind(':notes', '信用额度转换为余额');
                    $this->db->execute();
                }
            }
            
            // 设置所有用户的信用额度为0
            $this->db->query("UPDATE users SET credit_limit = 0, used_credit = 0");
            $this->db->execute();
            
            $this->db->commit();
            return $migrated_count;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
} 