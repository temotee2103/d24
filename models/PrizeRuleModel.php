<?php
class PrizeRuleModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // 获取所有奖金规则
    public function getAllPrizeRules() {
        $stmt = $this->db->prepare("SELECT * FROM prize_rules ORDER BY prize_type");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 通过ID获取奖金规则
    public function getPrizeRuleById($id) {
        $stmt = $this->db->prepare("SELECT * FROM prize_rules WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 通过类型和大小获取奖金规则
    public function getPrizeRuleByTypeAndSize($prize_type, $size_type) {
        $stmt = $this->db->prepare("SELECT * FROM prize_rules WHERE prize_type = ? AND size_type = ?");
        $stmt->execute([$prize_type, $size_type]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 更新奖金规则
    public function updatePrizeRule($id, $amount) {
        $stmt = $this->db->prepare("UPDATE prize_rules SET amount = ? WHERE id = ?");
        return $stmt->execute([$amount, $id]);
    }
    
    // 创建奖金规则
    public function createPrizeRule($data) {
        $stmt = $this->db->prepare("INSERT INTO prize_rules (prize_type, size_type, amount) VALUES (?, ?, ?)");
        return $stmt->execute([$data['prize_type'], $data['size_type'], $data['amount']]);
    }
    
    // 删除奖金规则
    public function deletePrizeRule($id) {
        $stmt = $this->db->prepare("DELETE FROM prize_rules WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // 格式化奖金规则用于显示
    public function getPrizeRulesFormatted() {
        $rules = $this->getAllPrizeRules();
        $formatted = [];
        
        foreach ($rules as $rule) {
            if (!isset($formatted[$rule['prize_type']])) {
                $formatted[$rule['prize_type']] = [];
            }
            
            $formatted[$rule['prize_type']][$rule['size_type']] = $rule['amount'];
        }
        
        return $formatted;
    }
} 