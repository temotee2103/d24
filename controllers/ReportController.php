<?php
class ReportController {
    private $transactionModel;
    
    public function __construct() {
        $this->transactionModel = new TransactionModel();
        AuthController::requireLogin();
    }
    
    // 显示销售报表
    public function sales() {
        // 只有管理员才能查看销售报表
        AuthController::requireAdmin();
        
        // 默认显示当月的报表
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        
        // 如果有请求特定日期范围
        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
        }
        
        // 获取销售统计数据
        $salesStats = $this->transactionModel->getSalesStats($start_date . ' 00:00:00', $end_date . ' 23:59:59');
        
        // 加载视图
        include_once ROOT_PATH . '/views/report/sales.php';
    }
    
    // 显示佣金报表
    public function commission() {
        $user_id = $_SESSION['user']['id'];
        $userModel = new UserModel();
        $user = $userModel->getUserById($user_id);
        
        // 默认显示当月的报表
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        
        // 如果有请求特定日期范围
        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
        }
        
        // 获取佣金统计数据
        if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
            // 管理员可以查看所有用户的佣金
            $commissionStats = $this->transactionModel->getCommissionStats(null, $start_date . ' 00:00:00', $end_date . ' 23:59:59');
            $commissionTransactions = $this->transactionModel->getTransactionsByType('commission');
        } else {
            // 普通用户只能查看自己的佣金
            $commissionStats = $this->transactionModel->getCommissionStats($user_id, $start_date . ' 00:00:00', $end_date . ' 23:59:59');
            $commissionTransactions = $this->transactionModel->getTransactionsByType('commission', $user_id);
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/report/commission.php';
    }
    
    // 显示交易历史
    public function transactions() {
        $user_id = $_SESSION['user']['id'];
        $userModel = new UserModel();
        $user = $userModel->getUserById($user_id);
        
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
        
        // 加载用户模型
        $userModel = new UserModel();
        
        // 获取用户统计数据
        $totalUsers = $userModel->getTotalUsers();
        $activeUsers = $userModel->getActiveUsers($start_date, $end_date);
        $newUsersToday = $userModel->getNewUsers(date('Y-m-d'));
        
        // 获取用户增长趋势数据
        $days = 30; // 默认显示30天
        if (isset($_GET['days']) && is_numeric($_GET['days'])) {
            $days = intval($_GET['days']);
        }
        $userGrowthData = $userModel->getUserGrowthData($days);
        
        // 获取用户类型分布
        $userTypeDistribution = $userModel->getUserTypeDistribution();
        
        // 获取活跃用户排行
        $topActiveUsers = $userModel->getTopActiveUsers(10, $start_date, $end_date);
        
        // 加载视图
        include_once ROOT_PATH . '/views/report/user.php';
    }
} 