<?php
class Utility {
    // 生成唯一的订单号
    public static function generateOrderNumber() {
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        return $date . $random;
    }
    
    // 过滤输入
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    // 验证是否是超级管理员
    public static function isSuperAdmin() {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'super_admin';
    }
    
    // 验证是否是管理员
    public static function isAdmin() {
        return isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super_admin');
    }
    
    // 验证是否已登录
    public static function isLoggedIn() {
        return isset($_SESSION['user']);
    }
    
    // 重定向到指定页面
    public static function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    // 获取当前页面URL
    public static function currentUrl() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    
    // 生成CSRF令牌
    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // 验证CSRF令牌
    public static function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
    }
    
    // 错误信息显示
    public static function showError($message) {
        return "<div class='alert alert-danger'>{$message}</div>";
    }
    
    // 成功信息显示
    public static function showSuccess($message) {
        return "<div class='alert alert-success'>{$message}</div>";
    }
} 