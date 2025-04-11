<?php
class HomeController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }
    
    // 首页
    public function index() {

        AuthController::requireLogin();
        
        $user_id = $_SESSION['user']['id']; 
        
        // Test database query 1
        try {
            $user = $this->userModel->getUserById($user_id);
            if (!$user) {
                 // Maybe redirect or show error if user becomes null?
                 throw new Exception("Logged in user data not found in database.");
            }
        } catch (Exception $e) {
             // Log the error instead of echoing
             error_log("Exception during getUserById in HomeController: " . $e->getMessage());
             // Redirect to login or show error page
             redirect('auth/login'); 
        }
        
        // Test session update
        $_SESSION['user'] = $user;
        
        // Test database query 2
        try {
            // Use isset check before accessing array key
            $subagentCount = isset($user['id']) ? $this->userModel->countSubagents($user['id']) : -1; 
            // Assign to user array if needed by view
            $user['subagent_count'] = $subagentCount; 
        } catch (Exception $e) {
             error_log("Exception during countSubagents in HomeController: " . $e->getMessage());
             // Allow page to load but maybe show placeholder for count?
             $user['subagent_count'] = '?'; 
        }

        // Assign other variables
        $stats = ['orders_today' => 0, 'online_users' => 0];
        $title = '仪表盘';
        
        // Test view inclusion
        include_once ROOT_PATH . '/views/home/index.php'; 
    }
    
    // 关于页面
    public function about() {
        $title = '关于我们';
        include_once ROOT_PATH . '/views/home/about.php';
    }
} 