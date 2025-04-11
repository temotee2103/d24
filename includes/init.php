<?php
// 启动会话
session_start();

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 设置字符集
header('Content-Type: text/html; charset=utf-8');

// 加载配置
$config = require_once __DIR__ . '/../config/config.php';

// 加载日志工具
require_once __DIR__ . '/Logger.php';

// 加载投注系统
require_once __DIR__ . '/BettingSystem.php';
require_once __DIR__ . '/BetParser.php';

// 设置时区
date_default_timezone_set($config['timezone']);

// 自定义错误处理函数
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // 该错误级别未包含在error_reporting中
        return false;
    }

    // 创建自定义错误日志目录（如果不存在）
    $log_dir = ROOT_PATH . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    // 记录详细错误到日志文件
    $error_message = sprintf("[%s] PHP Error [$errno]: %s in %s on line %d", 
        date('Y-m-d H:i:s'), $errstr, $errfile, $errline);
    
    $debug_backtrace = debug_backtrace();
    $backtrace_info = '';
    if (!empty($debug_backtrace)) {
        foreach ($debug_backtrace as $index => $trace) {
            if ($index > 8) break; // 限制堆栈深度
            $file = isset($trace['file']) ? $trace['file'] : '[internal function]';
            $line = isset($trace['line']) ? $trace['line'] : '';
            $function = isset($trace['function']) ? $trace['function'] : '';
            $class = isset($trace['class']) ? $trace['class'] . '::' : '';
            $backtrace_info .= sprintf("\n  #%d %s%s() called at [%s:%s]", 
                $index, $class, $function, $file, $line);
        }
    }
    
    // 将错误和堆栈跟踪写入日志文件
    error_log($error_message . $backtrace_info . "\n\n", 3, $log_dir . '/error.log');
    
    // 开发模式下显示错误
    if(isset($GLOBALS['config']['debug_mode']) && $GLOBALS['config']['debug_mode']) {
        echo "<b>PHP Error [$errno]</b>: $errstr in <b>$errfile</b> on line <b>$errline</b><br>";
        if (!empty($backtrace_info)) {
            echo "<pre>Backtrace: $backtrace_info</pre>";
        }
    }
    
    // 不执行PHP内部错误处理
    return true;
}
set_error_handler("custom_error_handler");

// 自定义异常处理函数
function custom_exception_handler($exception) {
    // 创建自定义错误日志目录（如果不存在）
    $log_dir = ROOT_PATH . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    // 记录异常到日志文件
    $error_message = sprintf("[%s] Uncaught Exception: %s in %s on line %d", 
        date('Y-m-d H:i:s'), $exception->getMessage(), $exception->getFile(), $exception->getLine());
    
    // 获取异常栈跟踪
    $trace = $exception->getTraceAsString();
    
    // 写入日志文件
    error_log($error_message . "\nStack trace:\n" . $trace . "\n\n", 3, $log_dir . '/error.log');
    
    // 开发模式下显示异常
    if(isset($GLOBALS['config']['debug_mode']) && $GLOBALS['config']['debug_mode']) {
        echo "<h1>系统发生错误</h1>";
        echo "<p><strong>Uncaught Exception:</strong> " . $exception->getMessage() . "</p>";
        echo "<p>File: " . $exception->getFile() . " on line " . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    } else {
        // 生产环境只显示友好的错误信息
        http_response_code(500);
        include ROOT_PATH . '/views/error/error.php';
    }
}
set_exception_handler("custom_exception_handler");

// 自动加载类
spl_autoload_register(function ($class_name) {
    // 检查类在不同目录的位置
    $dirs = [
        __DIR__ . '/' . $class_name . '.php',  // 在includes目录
        __DIR__ . '/../controllers/' . $class_name . '.php',  // 在controllers目录
        __DIR__ . '/../models/' . $class_name . '.php'  // 在models目录
    ];
    
    foreach ($dirs as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Define application root path EARLY
// define('ROOT_PATH', realpath(__DIR__ . '/..')); // REMOVED from here

// 定义应用程序URL
define('BASE_URL', $config['base_url']);

// 辅助函数
function url($path = '') {
    if (empty($path)) {
        return BASE_URL;
    }
    
    // 确保路径和BASE_URL之间只有一个斜杠
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function asset($path) {
    return url('assets/' . $path);
}

function redirect($url) {
    // 为调试输出重定向地址
    if(isset($GLOBALS['config']['debug_mode']) && $GLOBALS['config']['debug_mode']) {
        error_log("Redirecting to: " . url($url));
    }
    
    header("Location: " . url($url));
    exit;
}

// 处理XSS攻击
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 简单消息闪存功能
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null; 
}
