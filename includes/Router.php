<?php
class Router {
    private $routes = [];
    private $config;
    
    public function __construct() {
        $this->config = require_once __DIR__ . '/../config/config.php';
    }
    
    // 添加路由
    public function add($path, $controller, $action) {
        $this->routes[$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    // 解析URL
    public function parse($url) {
        // 确保URL不为null
        if ($url === null || !is_string($url)) {
            $url = '/';
        }
        
        // 解析URL，获取路径
        $url_parts = parse_url($url);
        
        // 检查parse_url是否返回失败
        if ($url_parts === false || !isset($url_parts['path'])) {
            $path = '/';
        } else {
            $path = $url_parts['path'];
        }
        
        // 删除基础路径前缀
        if (isset($this->config['base_url']) && is_string($this->config['base_url'])) {
            $base_url_parts = parse_url($this->config['base_url']);
            if ($base_url_parts !== false && isset($base_url_parts['path'])) {
                $base_path = $base_url_parts['path'];
                if ($base_path !== '/' && strpos($path, $base_path) === 0) {
                    $path = substr($path, strlen($base_path));
                }
            }
        }
        
        // 处理空路径
        if (empty($path) || $path === '/') {
            $path = isset($this->config['default_controller']) ? $this->config['default_controller'] : 'home';
        }
        
        // 去除前导和尾部斜杠
        $path = trim($path, '/');
        
        // 分割路径，获取控制器、动作和参数
        $parts = explode('/', $path);
        
        // 设置默认值
        $default_controller = isset($this->config['default_controller']) ? $this->config['default_controller'] : 'home';
        $default_action = isset($this->config['default_action']) ? $this->config['default_action'] : 'index';
        
        // 获取控制器和动作
        $controller = !empty($parts[0]) ? $parts[0] : $default_controller;
        $action = isset($parts[1]) && !empty($parts[1]) ? $parts[1] : $default_action;
        
        // 获取其余参数
        $params = array_slice($parts, 2);
        
        return [
            'controller' => $controller,
            'action' => $action,
            'params' => $params
        ];
    }
    
    // 调度请求
    public function dispatch() {
        // 获取当前URL，确保不为null
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        
        // 解析URL
        $route = $this->parse($url);
        
        // 获取控制器类名
        $controller_class = ucfirst($route['controller']) . 'Controller';
        
        // 检查控制器是否存在
        if (!class_exists($controller_class)) {
            $this->handleError(404, '找不到页面');
            return;
        }
        
        // 创建控制器实例
        $controller = new $controller_class();
        
        // 检查动作是否存在
        if (!method_exists($controller, $route['action'])) {
            $this->handleError(404, '找不到页面');
            return;
        }
        
        // 调用动作
        call_user_func_array([$controller, $route['action']], $route['params']);
    }
    
    // 处理错误
    private function handleError($code, $message) {
        http_response_code($code);
        include ROOT_PATH . '/views/error/error.php';
    }
} 