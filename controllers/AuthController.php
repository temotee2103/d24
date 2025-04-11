<?php
class AuthController {
    private $userModel;
    
    public function __construct() {
        // echo "DEBUG: Checkpoint CONSTRUCT - Inside AuthController Constructor"; exit(); // REMOVE CHECKPOINT
        $this->userModel = new UserModel();
    }
    
    // 显示登录页面
    public function login() {
        if (isset($_SESSION['user'])) {
            redirect('home');
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // echo "DEBUG: Login POST Reached - Method Start"; exit(); // REMOVED
             
             // Restore original logic
             if (empty($_POST['username']) || empty($_POST['password'])) {
                 $error = '请输入用户名和密码';
                 // Use popup or error display method
                 $popup_message = ['type' => 'error', 'text' => $error];
                 include_once ROOT_PATH . '/views/auth/login.php'; // Need to include view to show error
                 return; // Stop execution here if error
             } else {
                 $username = $_POST['username'];
                 $password = $_POST['password'];
                 
                 $user = $this->userModel->getUserByUsername($username);
                 
                 if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                     $_SESSION['user'] = $user;
                     $this->userModel->logUserActivity($user['id'], 'login', '用户登录成功');
                     redirect('home');
                 } else {
                     $error = '用户名或密码错误';
                     $popup_message = ['type' => 'error', 'text' => $error];
                     include_once ROOT_PATH . '/views/auth/login.php'; // Need to include view to show error
                     return; // Stop execution here if error
                 }
             }
        }
        // GET request shows form or if errors occurred in POST
        include_once ROOT_PATH . '/views/auth/login.php';
    }
    
    // 登出
    public function logout() {
        // 清除会话
        unset($_SESSION['user']);
        session_destroy();
        
        // 重定向到登录页面
        redirect('auth/login');
    }
    
    // 检查是否已登录（中间件功能）
    public static function requireLogin() {
        if (!isset($_SESSION['user'])) {
            redirect('auth/login');
        }
    }
    
    // 检查是否是管理员（中间件功能）
    public static function requireAdmin() {
        self::requireLogin();
        
        if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin') {
            set_flash_message('error', '您没有权限访问该页面');
            redirect('home');
        }
    }
    
    // 检查是否是超级管理员（中间件功能）
    public static function requireSuperAdmin() {
        self::requireLogin();
        
        if ($_SESSION['user']['role'] !== 'super_admin') {
            set_flash_message('error', '您没有权限访问该页面');
            redirect('home');
        }
    }
} 