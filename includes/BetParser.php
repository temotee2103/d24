<?php
class BetParser {
    // 解析下注输入
    public function parse($input) {
        $result = ['valid' => false, 'items' => [], 'error' => '', 'total' => 0];
        
        // 清理输入
        $input = trim($input);
        if (empty($input)) {
            $result['error'] = '输入不能为空';
            return $result;
        }
        
        // 分割多行输入
        $lines = explode("\n", $input);
        $totalBet = 0;
        $allItems = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // 针对每行单独解析
            $lineResult = $this->parseLine($line);
            
            if (!$lineResult['valid']) {
                return $lineResult; // 如果有任何一行解析错误，直接返回错误
            }
            
            // 收集结果
            $allItems = array_merge($allItems, $lineResult['items']);
            $totalBet += $lineResult['total'];
        }
        
        if (empty($allItems)) {
            $result['error'] = '没有有效的下注内容';
            return $result;
        }
        
        $result['valid'] = true;
        $result['items'] = $allItems;
        $result['total'] = $totalBet;
        
        return $result;
    }
    
    // 解析单行输入
    private function parseLine($input) {
        $result = ['valid' => false, 'items' => [], 'error' => '', 'total' => 0];
        
        // D格式解析
        if (strpos($input, 'D') === 0) {
            return $this->parseDFormat($input);
        }
        
        // #格式解析
        if (strpos($input, '#') === 0) {
            return $this->parseHashFormat($input);
        }
        
        // 其他格式解析
        if (preg_match('/^[0-9]+/', $input)) {
            return $this->parseNumberFormat($input);
        }
        
        $result['error'] = '未识别的格式: ' . $input;
        return $result;
    }
    
    // 解析D格式 (D1, D2, D3, D4)
    private function parseDFormat($input) {
        $result = ['valid' => false, 'items' => [], 'error' => '', 'total' => 0];
        
        // 从输入中提取D类型和数字
        if (preg_match('/^D([1-9])?(.*)$/i', $input, $matches)) {
            $type = isset($matches[1]) ? $matches[1] : '';
            $amount = isset($matches[2]) ? trim($matches[2]) : '';
            
            // 如果没有跟随金额，设置为0但不报错
            if (empty($amount) || !is_numeric($amount)) {
                $amount = 0;
            }
            
            // 获取期数描述
            $periodCount = empty($type) ? 1 : (int)$type;
            
            // 构建描述字符串
            $description = '';
            if ($periodCount == 1) {
                $description = '下注最近一期';
            } else {
                $description = '下注' . $periodCount . '期 (最近' . $periodCount . '期)';
            }
            
            $result['items'][] = [
                'type' => 'D' . $type,
                'amount' => (float)$amount,
                'description' => $description,
                'lottery_type' => ['M', 'P', 'T', 'S'], // 默认所有彩种
                'bet_type' => '1B 1S', // 默认为1大1小
                'number' => '1234' // 默认号码
            ];
            
            $result['valid'] = true;
            $result['total'] = (float)$amount;
        } else {
            $result['error'] = 'D格式不正确';
        }
        
        return $result;
    }
    
    // 解析#格式 (#1, #2, #34 等)
    private function parseHashFormat($input) {
        $result = ['valid' => false, 'items' => [], 'error' => '', 'total' => 0];
        
        // 从输入中提取#后跟随的数字
        if (preg_match('/^#([0-9]+)(.*)$/i', $input, $matches)) {
            $lottery_ids = str_split($matches[1]);
            $amount = isset($matches[2]) ? trim($matches[2]) : '';
            
            // 如果没有跟随金额，设置为0但不报错
            if (empty($amount) || !is_numeric($amount)) {
                $amount = 0;
            }
            
            // 彩种对应关系
            $lottery_types = ['M', 'P', 'T', 'S', 'B', 'K', 'W'];
            $selected_types = [];
            
            foreach ($lottery_ids as $id) {
                $id_num = (int)$id;
                if ($id_num >= 1 && $id_num <= count($lottery_types)) {
                    $selected_types[] = $lottery_types[$id_num-1];
                }
            }
            
            if (empty($selected_types)) {
                $selected_types = ['M', 'P', 'T', 'S']; // 默认MPTS
            }
            
            $result['items'][] = [
                'type' => '#' . $matches[1],
                'amount' => (float)$amount,
                'description' => '开奖号码' . $matches[1],
                'lottery_type' => $selected_types,
                'bet_type' => '1B 1S', // 默认为1大1小
                'number' => '1234' // 默认号码
            ];
            
            $result['valid'] = true;
            $result['total'] = (float)$amount;
        } else {
            $result['error'] = '#格式不正确';
        }
        
        return $result;
    }
    
    // 解析数字格式 (1234#1, 1234-1 等)
    private function parseNumberFormat($input) {
        $result = ['valid' => false, 'items' => [], 'error' => '', 'total' => 0];
        
        // 识别包含多个#的格式 (如 1234#1#1#1#1#1)
        if (preg_match('/^([0-9]+)((?:#[0-9]*)*)$/i', $input, $main_matches)) {
            $number = $main_matches[1]; // 号码部分，如"1234"
            $bet_parts = $main_matches[2]; // 包含所有#部分，如"#1#1#1#1#1"
            
            // 如果包含#号，进一步解析
            if (!empty($bet_parts)) {
                // 按#分割，第一个元素是空的，所以我们跳过它
                $parts = explode('#', $bet_parts);
                array_shift($parts); // 移除第一个空元素
                
                // 定义每个位置的含义
                $positions = [
                    0 => 'B', // 大 (Big)
                    1 => 'S', // 小 (Small)
                    2 => '4A', // 4A
                    3 => '4B', // 4B
                    4 => '4C'  // 4C
                ];
                
                // 用于存储最终的下注类型
                $bet_type_parts = [];
                $total_amount = 0;
                
                // 解析每个位置的下注
                foreach ($parts as $index => $part) {
                    if (isset($positions[$index]) && !empty($part) && is_numeric($part)) {
                        $amount = (int)$part;
                        if ($amount > 0) {
                            $bet_type_parts[] = $positions[$index] . $amount;
                            $total_amount += $amount * 4; // 每个下注类型对应4个号码位置
                        }
                    }
                }
                
                // 如果没有有效的下注类型，返回错误
                if (empty($bet_type_parts)) {
                    $result['error'] = '没有有效的下注类型';
                    return $result;
                }
                
                // 彩种默认为MPTS
                $lottery_types = ['M', 'P', 'T', 'S'];
                
                // 构建下注项
                $result['items'][] = [
                    'type' => $number . $bet_parts,
                    'description' => '号码' . $number . ' 类型 ' . implode(' ', $bet_type_parts),
                    'amount' => $total_amount,
                    'number' => $number,
                    'lottery_type' => $lottery_types,
                    'bet_type' => implode(' ', $bet_type_parts)
                ];
                
                $result['valid'] = true;
                $result['total'] = $total_amount;
                
                return $result;
            }
        }
        
        // 如果不是多#格式，使用原有的解析逻辑
        $patterns = [
            // 1234#1 格式 (号码#彩种)
            '/^([0-9]+)#([0-9]+)$/i' => function($matches) {
                $number = $matches[1];
                $lottery_id = $matches[2];
                
                // 彩种对应关系
                $lottery_types = ['M', 'P', 'T', 'S', 'B', 'K', 'W'];
                $lottery_type = isset($lottery_types[$lottery_id-1]) ? [$lottery_types[$lottery_id-1]] : ['M', 'P', 'T', 'S'];
                
                return [
                    'type' => $number . '#' . $lottery_id,
                    'description' => '号码' . $number . ' 位置' . $lottery_id,
                    'amount' => 8, // 默认1B 1S，每个4个位置
                    'number' => $number,
                    'lottery_type' => $lottery_type,
                    'bet_type' => 'B1 S1' // 默认为1大1小
                ];
            },
            // 1234-1 格式 (号码-类型)
            '/^([0-9]+)-([0-9A-Z]+)(.*)$/i' => function($matches) {
                $number = $matches[1];
                $bet_type = $matches[2];
                $amount = isset($matches[3]) && is_numeric(trim($matches[3])) ? (float)trim($matches[3]) : 0;
                
                // 下注类型对应关系
                $bet_type_map = [
                    '1' => 'B', // 大
                    '2' => 'S', // 小
                    '3' => '4A', // 4A
                    '4' => '4B', // 4B
                    '5' => '4C'  // 4C
                ];
                
                $bet_type_str = isset($bet_type_map[$bet_type]) ? "1" . $bet_type_map[$bet_type] : $bet_type;
                
                return [
                    'type' => $number . '-' . $bet_type,
                    'description' => '号码' . $number . ' 类型' . $bet_type,
                    'amount' => $amount == 0 ? 4 : $amount * 4, // 每个类型占4个位置
                    'number' => $number,
                    'lottery_type' => ['M', 'P', 'T', 'S'], // 默认所有类型
                    'bet_type' => $bet_type_str
                ];
            },
            // 纯数字格式如 123456
            '/^([0-9]+)$/i' => function($matches) {
                $number = $matches[1];
                
                return [
                    'type' => $number,
                    'description' => '号码' . $number,
                    'amount' => 8, // 默认B1 S1，总共8个位置
                    'number' => $number,
                    'lottery_type' => ['M', 'P', 'T', 'S'], // 默认所有类型
                    'bet_type' => 'B1 S1' // 默认为1大1小
                ];
            }
        ];
        
        foreach ($patterns as $pattern => $callback) {
            if (preg_match($pattern, $input, $matches)) {
                $item = $callback($matches);
                
                // 金额为0也是有效的，只是不会实际计入总金额
                $result['items'][] = $item;
                $result['valid'] = true;
                $result['total'] += $item['amount'];
                
                return $result;
            }
        }
        
        $result['error'] = '格式不被支持';
        return $result;
    }
} 