<?php
class HomeController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }
    
    // 首页
    public function index() {
        AuthController::requireLogin();
        
        // 获取当前用户ID
        $user_id = $_SESSION['user']['id'];
        
        // 强制从数据库获取最新的用户信息
        $user = $this->userModel->getUserById($user_id);
        
        // 更新session中的用户信息
        $_SESSION['user'] = $user;
        
        // 获取下线代理数量
        $user['subagent_count'] = $this->userModel->countSubagents($user_id);
        
        // 获取统计数据
        $stats = [
            'orders_today' => 0, // 这里应该从订单模型获取实际数据
            'online_users' => 0, // 这里应该从用户模型获取实际数据
        ];
        
        $title = '仪表盘';
        
        include_once ROOT_PATH . '/views/home/index.php';
    }
    
    // 关于页面
    public function about() {
        $title = '关于我们';
        include_once ROOT_PATH . '/views/home/about.php';
    }
} 