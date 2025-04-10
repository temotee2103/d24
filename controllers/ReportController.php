<?php
class ReportController {
    private $transactionModel;
    private $userModel;
    
    public function __construct() {
        $this->transactionModel = new TransactionModel();
        $this->userModel = new UserModel();
        AuthController::requireLogin();
    }
    
    /**
     * 显示综合财务报表
     */
    public function financial() {
        // 检查是否为管理员
        if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin') {
            redirect('home');
        }
        
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        
        try {
            // 尝试创建commissions表（如果不存在）
            try {
                $db = Database::getInstance();
                $conn = $db->getConnection();
                
                // 检查表是否存在
                $stmt = $conn->prepare("SHOW TABLES LIKE 'commissions'");
                $stmt->execute();
                $tableExists = $stmt->rowCount() > 0;
                
                if (!$tableExists) {
                    // 创建commissions表
                    $createTableSQL = "CREATE TABLE IF NOT EXISTS `commissions` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `user_id` int(11) NOT NULL,
                      `amount` decimal(10,2) NOT NULL,
                      `order_id` int(11) DEFAULT NULL,
                      `note` text DEFAULT NULL,
                      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                      PRIMARY KEY (`id`),
                      KEY `user_id` (`user_id`),
                      KEY `order_id` (`order_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                    
                    $conn->exec($createTableSQL);
                    
                    // 添加外键（如果需要）
                    try {
                        $conn->exec("ALTER TABLE `commissions` 
                                     ADD CONSTRAINT `fk_commission_user` 
                                     FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
                                     ON DELETE CASCADE");
                                     
                        $conn->exec("ALTER TABLE `commissions` 
                                     ADD CONSTRAINT `fk_commission_order` 
                                     FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) 
                                     ON DELETE SET NULL");
                    } catch (Exception $e) {
                        // 忽略外键错误，可能是数据库不支持或已存在
                    }
                }
            } catch (Exception $e) {
                // 忽略表创建错误，继续显示报表
            }
            
            // 获取订单和佣金数据
            $orderModel = new OrderModel();
            
            // 获取所有订单
            $orders = $orderModel->getOrdersByDateRange($startDate, $endDate);
            
            // 尝试获取佣金数据，如果表不存在则提供备用数据
            try {
                $commissionModel = new CommissionModel();
                $commissions = $commissionModel->getCommissionsByDateRange($startDate, $endDate);
            } catch (Exception $e) {
                // 如果佣金表不存在，使用空数组
                $commissions = [];
            }
            
            // 处理数据，计算每个订单的佣金和利润
            $financialRecords = [];
            $totalSales = 0;
            $totalCommission = 0;
            $totalProfit = 0;
            
            // 按日期对数据进行分组（用于图表）
            $chartData = [
                'dates' => [],
                'sales' => [],
                'commission' => [],
                'profit' => []
            ];
            
            $dateData = [];
            
            foreach ($orders as $order) {
                $orderAmount = floatval($order['total_amount']);
                $orderCommission = 0;
                
                // 查找与订单相关的佣金记录
                foreach ($commissions as $commission) {
                    if (isset($commission['order_id']) && $commission['order_id'] == $order['id']) {
                        $orderCommission += floatval($commission['amount']);
                    }
                }
                
                // 计算利润和利润率
                $profit = $orderAmount - $orderCommission;
                $profitMargin = $orderAmount > 0 ? ($profit / $orderAmount * 100) : 0;
                
                // 添加到记录数组
                $financialRecords[] = [
                    'order_number' => $order['order_number'],
                    'username' => $order['username'],
                    'amount' => $orderAmount,
                    'commission' => $orderCommission,
                    'profit' => $profit,
                    'profit_margin' => $profitMargin,
                    'created_at' => $order['created_at']
                ];
                
                // 累计总额
                $totalSales += $orderAmount;
                $totalCommission += $orderCommission;
                
                // 按日期分组数据
                $orderDate = date('Y-m-d', strtotime($order['created_at']));
                if (!isset($dateData[$orderDate])) {
                    $dateData[$orderDate] = [
                        'sales' => 0,
                        'commission' => 0,
                        'profit' => 0
                    ];
                }
                
                $dateData[$orderDate]['sales'] += $orderAmount;
                $dateData[$orderDate]['commission'] += $orderCommission;
                $dateData[$orderDate]['profit'] += $profit;
            }
            
            // 计算总利润和利润率
            $totalProfit = $totalSales - $totalCommission;
            $profitMargin = $totalSales > 0 ? ($totalProfit / $totalSales * 100) : 0;
            
            // 准备图表数据
            $chartDates = array_keys($dateData);
            sort($chartDates); // 按日期排序
            
            foreach ($chartDates as $date) {
                $chartData['dates'][] = "'" . date('m/d', strtotime($date)) . "'";
                $chartData['sales'][] = $dateData[$date]['sales'];
                $chartData['commission'][] = $dateData[$date]['commission'];
                $chartData['profit'][] = $dateData[$date]['profit'];
            }
            
            // 合并统计数据
            $financialStats = [
                'total_sales' => $totalSales,
                'total_commission' => $totalCommission,
                'net_profit' => $totalProfit,
                'profit_margin' => $profitMargin
            ];
            
            // 按创建时间排序记录
            usort($financialRecords, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // 渲染视图
            $data = [
                'financialRecords' => $financialRecords,
                'financialStats' => $financialStats,
                'chartData' => $chartData
            ];
            
            include_once ROOT_PATH . '/views/report/financial.php';
        } catch (Exception $e) {
            // 发生错误时显示错误页面
            $_SESSION['error'] = "报表生成错误：" . $e->getMessage();
            include_once ROOT_PATH . '/views/error/index.php';
        }
    }
    
    // 显示交易历史
    public function transactions() {
        $user_id = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($user_id);
        
        // 设置筛选条件
        $filters = [];
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] . ' 00:00:00' : date('Y-m-d', strtotime('-30 days')) . ' 00:00:00';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
        $filters['date_range'] = [$start_date, $end_date];
        
        // 交易类型筛选
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            $filters['type'] = $_GET['type'];
        }
        
        // 金额筛选
        if (isset($_GET['min_amount']) && is_numeric($_GET['min_amount'])) {
            $filters['min_amount'] = floatval($_GET['min_amount']);
        }
        
        // 根据用户角色获取不同数据
        if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
            // 管理员可以查看所有交易
            $transactions = $this->transactionModel->getFilteredTransactions($filters);
        } else {
            // 普通用户只能查看自己的交易
            $filters['user_id'] = $user_id;
            $transactions = $this->transactionModel->getFilteredTransactions($filters);
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/report/transactions.php';
    }
    
    // 显示用户统计报表
    public function user() {
        // 只有管理员才能查看用户报表
        AuthController::requireAdmin();
        
        // 默认显示当月的报表
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        
        // 如果有请求特定日期范围
        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
        }
        
        // 获取所有普通用户、代理和管理员（不包括super_admin）
        $users = $this->userModel->getAllUsersExceptRole('super_admin');
        
        // 确保没有重复的用户ID
        $uniqueUsers = [];
        foreach ($users as $user) {
            $uniqueUsers[$user['id']] = $user;
        }
        
        $displayUsers = [];
        foreach ($uniqueUsers as $user) {
            // 获取用户充值总额
            $deposits = $this->transactionModel->getTransactionsByType('deposit', $user['id']);
            $total_deposits = 0;
            foreach ($deposits as $deposit) {
                $total_deposits += (float)$deposit['amount'];
            }
            $user['total_deposits'] = $total_deposits;
            
            // 获取用户消费总额
            $withdrawals = $this->transactionModel->getTransactionsByType('withdraw', $user['id']);
            $total_spent = 0;
            foreach ($withdrawals as $withdrawal) {
                $total_spent += (float)$withdrawal['amount'];
            }
            $user['total_spent'] = $total_spent;
            
            // 获取用户佣金总额
            $commissions = $this->transactionModel->getTransactionsByType('commission', $user['id']);
            $commission_balance = 0;
            foreach ($commissions as $commission) {
                $commission_balance += (float)$commission['amount'];
            }
            $user['commission_balance'] = $commission_balance;
            
            // 获取用户登录次数和最后登录时间
            $login_logs = $this->userModel->getUserLogs($user['id'], 'login');
            $user['login_count'] = count($login_logs);
            $user['last_login'] = !empty($login_logs) ? $login_logs[0]['created_at'] : null;
            
            $displayUsers[] = $user;
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/report/user.php';
    }
    
    /**
     * 显示佣金余额报表
     */
    public function commission() {
        // 检查用户是否已登录
        AuthController::requireLogin();
        
        // 获取用户ID
        $user_id = $_SESSION['user']['id'];
        
        // 获取用户信息
        $userModel = new UserModel();
        $user = $userModel->getUserById($user_id);
        
        // 默认显示当月的佣金
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        
        try {
            // 初始化CommissionModel
            $commissionModel = new CommissionModel();
            
            // 根据用户角色获取不同数据
            if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
                // 管理员可以查看所有佣金总额
                $totalCommission = $commissionModel->getCommissionStats($startDate, $endDate);
            } else {
                // 普通用户只能查看自己的佣金总额
                $totalCommission = $commissionModel->getCommissionStats($startDate, $endDate, $user_id);
            }
            
            // 加载视图
            include_once ROOT_PATH . '/views/report/commission.php';
        } catch (Exception $e) {
            // 错误处理
            $_SESSION['error'] = "获取佣金数据时发生错误：" . $e->getMessage();
            redirect('home');
        }
    }
} 