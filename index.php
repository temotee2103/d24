<?php
// 启用错误报告便于调试
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 加载初始化文件
require_once __DIR__ . '/includes/init.php';

// Include necessary files
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/Logger.php';  // Add the Logger class include
require_once 'includes/DB.php';
require_once 'includes/BetParser.php';
require_once 'models/UserModel.php';
require_once 'models/OrderModel.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/OrderController.php';
require_once 'controllers/HomeController.php';

// 获取请求的URL路径
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// 去除脚本名称和查询字符串，获取纯路径
$base_path = dirname($script_name);
// 确保URL中的d24目录被正确处理
if (strpos($request_uri, '/d24/') !== false) {
    $path = str_replace('/d24/', '', $request_uri);
} else {
    $path = str_replace($base_path, '', $request_uri);
}
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// 如果路径为空，使用默认控制器
if (empty($path)) {
    $path = 'auth/login';
}

// 创建路由器实例
$router = new Router();

// 添加路由
// 认证路由
$router->add('auth/login', 'Auth', 'login');
$router->add('auth/logout', 'Auth', 'logout');

// 首页路由
$router->add('home', 'Home', 'index');
$router->add('home/about', 'Home', 'about');

// 用户路由
$router->add('user', 'User', 'index');
$router->add('user/subagents', 'User', 'subagents');
$router->add('user/create', 'User', 'create');
$router->add('user/edit', 'User', 'edit');
$router->add('user/view', 'User', 'view');
$router->add('user/deposit', 'User', 'deposit');
$router->add('user/migrateCredit', 'User', 'migrateCredit');
$router->add('user/check_balance', 'User', 'check_balance');
$router->add('user/sync_balance', 'User', 'sync_balance');
$router->add('user/delete', 'User', 'delete');
$router->add('user/batch_action', 'User', 'batch_action');
$router->add('user/toggle_status', 'User', 'toggle_status');

// 订单路由
$router->add('order', 'Order', 'index');
$router->add('order/create', 'Order', 'create');
$router->add('order/view', 'Order', 'view');
$router->add('order/cancel', 'Order', 'cancel');

// 报表路由
$router->add('report/sales', 'Report', 'sales');
$router->add('report/commission', 'Report', 'commission');
$router->add('report/transactions', 'Report', 'transactions');
$router->add('report/user', 'Report', 'user');

// 直接调度请求
try {
    $parts = explode('/', $path);
    $controller = !empty($parts[0]) ? $parts[0] : 'home';
    $action = isset($parts[1]) ? $parts[1] : 'index';
    $params = array_slice($parts, 2);
    
    // 格式化控制器类名
    $controller_class = ucfirst($controller) . 'Controller';
    
    // 检查控制器是否存在
    if (!class_exists($controller_class)) {
        throw new Exception("控制器 {$controller_class} 不存在");
    }
    
    // 创建控制器实例
    $controller_instance = new $controller_class();
    
    // 检查动作是否存在
    if (!method_exists($controller_instance, $action)) {
        throw new Exception("动作 {$action} 在控制器 {$controller_class} 中不存在");
    }
    
    // 调用动作
    call_user_func_array([$controller_instance, $action], $params);
} catch (Exception $e) {
    // 错误处理
    http_response_code(500);
    $code = 500;
    $message = $e->getMessage();
    include ROOT_PATH . '/views/error/error.php';
} 