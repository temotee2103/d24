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
        $current_user_id = $_SESSION['user']['id'];
        $current_user_role = $_SESSION['user']['role'];
        $currentUser = $this->userModel->getUserById($current_user_id); // Fetch viewing user data
        $currentUserRate = $currentUser['commission_rate'];

        // Allow only logged-in users (can be refined later if needed)
        // AuthController::requireLogin(); // Already called in constructor

        // Date range setup
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

        try {
            $orderModel = new OrderModel();
            $commissionModel = new CommissionModel();
            $userModel = $this->userModel; // Use injected model

            $ordersForSales = []; // Orders relevant for sales calculation
            $totalSales = 0;
            $reportProfit = 0; // Commission EARNED by viewer
            $profitMargin = 0;
            $chartData = [
                'dates' => [],
                'sales' => [], 
                'profit' => []  // Represents Personal Commission Earned by viewer
            ];
            $dateData = [];

            // Fetch personal commission earned by the viewer (needed for chart, and for admin profit)
            $personalCommissionRecords = $commissionModel->getCommissionsByDateRangeAndUser($startDate, $endDate, $current_user_id);
            $personalCommissionTotalForChart = 0;
            foreach($personalCommissionRecords as $c) { $personalCommissionTotalForChart += floatval($c['amount']); }
            Logger::log('Financial Report: Personal Commission Total (from commission table)', ['user_id' => $current_user_id, 'start' => $startDate, 'end' => $endDate, 'commission' => $personalCommissionTotalForChart]);

            if ($current_user_role === 'admin' || $current_user_role === 'super_admin') {
                // --- Admin / Super Admin View --- 
                // Sales = System-wide sales
                $ordersForSales = $orderModel->getOrdersByDateRange($startDate, $endDate);
                foreach ($ordersForSales as $order) {
                    $totalSales += floatval($order['total_amount']);
                }
                // Profit for Admin = Personal Commission Earned (from commission table)
                $reportProfit = $personalCommissionTotalForChart; 
                Logger::log('Financial Report: Admin/SuperAdmin Data', ['total_orders' => count($ordersForSales), 'total_sales' => $totalSales, 'report_profit' => $reportProfit]);
            
            } else { // Agent View
                // --- Agent View ---
                // Fetch Agent's OWN orders
                $agentOrders = $orderModel->getUserOrdersByDateRange($current_user_id, $startDate, $endDate);
                $agentSales = 0;
                foreach ($agentOrders as $order) {
                    $agentSales += floatval($order['total_amount']);
                }
                
                // Fetch Subagent orders
                $subagentOrders = $orderModel->getSubagentOrdersByDateRange($current_user_id, $startDate, $endDate);
                $subagentSales = 0;
                foreach ($subagentOrders as $order) {
                    $subagentSales += floatval($order['total_amount']);
                }

                // Total Sales for Agent's view = Own Sales + Subagent Sales
                $totalSales = $agentSales + $subagentSales;
                
                // --- Calculate Profit (Commission Earned by Agent) ---
                $reportProfit = 0;
                // 1. Commission from Own Sales (based on agent's own rate)
                $reportProfit += $agentSales * $currentUserRate / 100;
                Logger::log('Financial Report: Agent Profit - From Own Sales', ['profit_own' => $agentSales * $currentUserRate / 100]);

                // 2. Commission from Direct Subagents
                $directSubagents = $userModel->getSubagents($current_user_id);
                $commissionFromSubs = 0;
                foreach ($directSubagents as $subagent) {
                    $subagentRate = $subagent['commission_rate'];
                    $rateDiff = $currentUserRate - $subagentRate;
                    if ($rateDiff > 0) {
                        $subagentOrdersForComm = $orderModel->getUserOrdersByDateRange($subagent['id'], $startDate, $endDate);
                        foreach ($subagentOrdersForComm as $order) {
                            $commissionEarned = floatval($order['total_amount']) * $rateDiff / 100;
                            $commissionFromSubs += $commissionEarned;
                        }
                    }
                }
                $reportProfit += $commissionFromSubs;
                Logger::log('Financial Report: Agent Profit - From Sub Sales', ['profit_subs' => $commissionFromSubs]);

                // Combine orders for chart grouping (Sales)
                $ordersForSales = array_merge($agentOrders, $subagentOrders); 

                Logger::log('Financial Report: Agent Data', [
                    'agent_sales' => $agentSales,
                    'subagent_sales' => $subagentSales,
                    'total_sales' => $totalSales, 
                    'report_profit' => $reportProfit // Includes commission from own + subagents
                ]);
            }

            // Calculate Profit Margin (Viewer Earned Commission / Relevant Sales)
            $profitMargin = $totalSales > 0 ? ($reportProfit / $totalSales * 100) : 0;
            Logger::log('Financial Report: Calculated Margin', ['margin' => $profitMargin]);

            // --- Chart Data Calculation ---
            $dateData = []; // Reset dateData

            // 1. Group Relevant Sales (System or Agent Scope)
            foreach ($ordersForSales as $order) {
                 $orderDate = date('Y-m-d', strtotime($order['created_at']));
                 if (!isset($dateData[$orderDate])) {
                    $dateData[$orderDate] = ['sales' => 0, 'earned_commission' => 0];
                 }
                 $dateData[$orderDate]['sales'] += floatval($order['total_amount']);
            }

            // 2. Group Personal Commission Earned by Date 
            //    Use the already fetched records for admin, recalculate daily for agent
            if ($current_user_role === 'admin' || $current_user_role === 'super_admin') {
                 // Use the commission records fetched earlier
                 foreach ($personalCommissionRecords as $commission) {
                     $commDate = date('Y-m-d', strtotime($commission['created_at']));
                     if (!isset($dateData[$commDate])) { $dateData[$commDate] = ['sales' => 0, 'earned_commission' => 0]; }
                     $dateData[$commDate]['earned_commission'] += floatval($commission['amount']); 
                 }
            } else { // Agent
                // Recalculate daily earned commission for agent
                // Group Commission from Own Sales
                foreach($agentOrders as $order) {
                    $orderDate = date('Y-m-d', strtotime($order['created_at']));
                    if (!isset($dateData[$orderDate])) { $dateData[$orderDate] = ['sales' => 0, 'earned_commission' => 0]; }
                    $dateData[$orderDate]['earned_commission'] += floatval($order['total_amount']) * $currentUserRate / 100;
                }
                // Group Commission from Subagents
                foreach ($directSubagents as $subagent) {
                    $subagentRate = $subagent['commission_rate'];
                    $rateDiff = $currentUserRate - $subagentRate;
                    if ($rateDiff > 0) {
                        $subagentOrdersForComm = $orderModel->getUserOrdersByDateRange($subagent['id'], $startDate, $endDate);
                        foreach ($subagentOrdersForComm as $order) {
                            $orderDate = date('Y-m-d', strtotime($order['created_at']));
                            if (!isset($dateData[$orderDate])) { $dateData[$orderDate] = ['sales' => 0, 'earned_commission' => 0]; }
                            $dateData[$orderDate]['earned_commission'] += floatval($order['total_amount']) * $rateDiff / 100;
                        }
                    }
                }
            }
            Logger::log('Financial Report: Grouped Daily Data', $dateData);
            
            // 3. Prepare final chart data 
            $chartDates = array_keys($dateData);
            sort($chartDates);
            $chartData = ['dates' => [], 'sales' => [], 'profit' => []]; 
            foreach ($chartDates as $date) {
                $dailySales = $dateData[$date]['sales'] ?? 0;
                $dailyEarnedComm = $dateData[$date]['earned_commission'] ?? 0; 

                $chartData['dates'][] = "'" . date('m/d', strtotime($date)) . "'";
                $chartData['sales'][] = $dailySales;
                $chartData['profit'][] = $dailyEarnedComm; 
            }
            Logger::log('Financial Report: Final Chart Data', $chartData);
            
            // Final stats for the view
            $financialStats = [
                'total_sales' => $totalSales,      // Relevant Sales
                'net_profit' => $reportProfit,     // Viewer's Earned Commission (Own + Subagents for Agent)
                'profit_margin' => $profitMargin   // Margin based on above
            ];
            Logger::log('Financial Report: Final Stats for View', $financialStats);
            
            $financialRecords = []; // Keep detailed records empty
            
            // Render the view
            $data = [
                'financialRecords' => $financialRecords,
                'financialStats' => $financialStats,
                'chartData' => $chartData,
                'report_title' => ($current_user_role === 'agent') ? '代理财务概览' : '综合财务报表' // Dynamic title
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
        // Allow agent, admin, super_admin
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['agent', 'admin', 'super_admin'])) {
             set_flash_message('error', '您没有权限访问该页面');
             redirect('home');
             return; // Ensure exit after redirect
        }
        
        $current_user_role = $_SESSION['user']['role'];
        $current_user_id = $_SESSION['user']['id'];

        // 默认显示当月的报表
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        
        // 如果有请求特定日期范围
        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
        }
        
        // 获取要显示的用户列表
        if ($current_user_role === 'admin' || $current_user_role === 'super_admin') {
             // 管理员/超管获取所有非超管用户
             $users = $this->userModel->getAllUsersExceptRole('super_admin');
        } else { // Agent
            // 代理只获取自己的直接下线
            $users = $this->userModel->getSubagents($current_user_id); 
            // Maybe add self? Depends on requirements.
            // $self = $this->userModel->getUserById($current_user_id); 
            // if ($self) $users[] = $self; 
        }

        // 确保没有重复的用户ID (虽然上面逻辑可能已经保证了，但以防万一)
        $uniqueUsers = [];
        foreach ($users as $user) {
            if ($user) { // Ensure user data is valid
                $uniqueUsers[$user['id']] = $user;
            }
        }
        
        $displayUsers = [];
        $commissionModel = new CommissionModel(); // Instantiate CommissionModel here
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
            
            // 获取用户佣金总额 (from commissions table is better)
            $commission_balance = $commissionModel->getTotalUserCommission($user['id']); // Method needs to exist
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
    
    /* // REMOVED - Commission Report functionality is removed
    /**
     * 显示佣金余额报表
     */
    /*
    public function commission() {
        // ... method content ...
    }
    */
} 