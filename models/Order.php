<?php

class Order
{
    private $db;
    private $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger('Order');
    }

    public function create($user_id, $period_id, $bet_content, $total_amount, $items)
    {
        $this->logger->debug("开始创建订单: user_id=$user_id, period_id=$period_id, total_amount=$total_amount");
        $this->logger->debug("下注内容: $bet_content");
        $this->logger->debug("订单项目: " . print_r($items, true));
        
        try {
            // 开始事务
            $this->db->beginTransaction();
            $this->logger->debug("开始数据库事务");
            
            // 创建订单记录
            $sql = "INSERT INTO orders (user_id, period_id, bet_content, total_amount, created_at) 
                    VALUES (:user_id, :period_id, :bet_content, :total_amount, NOW())";
            
            $params = [
                ':user_id' => $user_id,
                ':period_id' => $period_id,
                ':bet_content' => $bet_content,
                ':total_amount' => $total_amount
            ];
            
            $this->logger->debug("执行SQL: $sql");
            $this->logger->debug("参数: " . print_r($params, true));
            
            $this->db->query($sql, $params);
            $order_id = $this->db->lastInsertId();
            
            if (!$order_id) {
                throw new Exception("创建订单失败，无法获取订单ID");
            }
            
            $this->logger->debug("订单创建成功，ID: $order_id");
            
            // 创建订单项目
            $this->logger->debug("开始创建订单项目，数量: " . count($items));
            foreach ($items as $item) {
                $sql = "INSERT INTO order_items (order_id, lottery_type, numbers, amount) 
                        VALUES (:order_id, :lottery_type, :numbers, :amount)";
                
                $params = [
                    ':order_id' => $order_id,
                    ':lottery_type' => $item['lottery_type'],
                    ':numbers' => $item['numbers'],
                    ':amount' => $item['amount']
                ];
                
                $this->logger->debug("执行SQL: $sql");
                $this->logger->debug("参数: " . print_r($params, true));
                
                $result = $this->db->query($sql, $params);
                
                if (!$result) {
                    throw new Exception("创建订单项目失败: lottery_type={$item['lottery_type']}, numbers={$item['numbers']}");
                }
            }
            
            // 提交事务
            $this->db->commit();
            $this->logger->debug("事务提交成功，订单创建完成");
            
            return ['success' => true, 'order_id' => $order_id];
        } catch (Exception $e) {
            // 回滚事务
            $this->db->rollBack();
            $this->logger->error("订单创建失败，事务回滚: " . $e->getMessage());
            $this->logger->error("异常堆栈: " . $e->getTraceAsString());
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // ... existing code ...
} 