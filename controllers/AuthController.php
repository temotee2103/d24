<?php
class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }
    
    // 显示登录页面
    public function login() {
        // 如果已经登录，跳转到首页
        if (isset($_SESSION['user'])) {
            redirect('home');
        }

        $error = '';
        
        // 检查表单是否已提交
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['username']) || empty($_POST['password'])) {
                $error = '请输入用户名和密码';
            } else {
                $username = $_POST['username'];
                $password = $_POST['password'];
                
                // 尝试认证用户
                $user = $this->userModel->getUserByUsername($username);
                
                if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                    // 设置用户会话 - 存储完整的用户信息
                    $_SESSION['user'] = $user;
                    
                    // 记录登录活动
                    $this->userModel->logUserActivity($user['id'], 'login', '用户登录成功');
                    
                    // 重定向到首页
                    redirect('home');
                } else {
                    $error = '用户名或密码错误';
                }
            }
        }
        
        // 显示登录表单
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