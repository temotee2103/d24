<?php
// Define DEBUG_MODE if not already defined
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

// At the top of the file, require the Logger if it's not already autoloaded
if (!class_exists('Logger')) {
    require_once __DIR__ . '/../includes/Logger.php';
}

class OrderController {
    private $orderModel;
    private $betParser;
    
    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->betParser = new BetParser();
        AuthController::requireLogin();
    }
    
    // 显示下注表单
    public function create() {
        // Initialize the Logger
        Logger::init();
        Logger::setDebugMode(defined('DEBUG_MODE') && DEBUG_MODE);
        
        // 添加更详细的日志
        Logger::log('========== 开始处理下注请求 ==========', [
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'post_data' => $_POST,
            'session_user' => isset($_SESSION['user']) ? ['id' => $_SESSION['user']['id'], 'username' => $_SESSION['user']['username']] : 'not logged in'
        ]);
        
        $error = '';
        $success = '';
        $parsed_result = null;
        $bet_content = '';
        $debug_info = '';
        $user = auth()->user(); // Get user data early
        $popup_message = null; // Initialize popup message
        
        // Get current and next periods
        $current_period = $this->getCurrentPeriod();
        $next_periods = $this->getNextPeriods(3);
        
        // Ensure log directory exists
        $log_dir = ROOT_PATH . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Logger::log('POST request received in order/create', $_POST);
            
            // Check for required fields
            $bet_content = isset($_POST['bet_content']) ? trim($_POST['bet_content']) : '';
            $preview = isset($_POST['preview']) ? (int)$_POST['preview'] : 0;
            $confirm = isset($_POST['confirm']) ? (int)$_POST['confirm'] : 0;
            
            Logger::log('Processing bet submission', [
                'bet_content' => $bet_content,
                'preview' => $preview,
                'confirm' => $confirm
            ]);
            
            // Validate bet content
            if (empty($bet_content)) {
                Logger::log('Error: Empty bet content', ['error' => 'Bet content cannot be empty']);
                $popup_message = ['type' => 'error', 'text' => '下注内容不能为空'];
                return $this->render('order/create', [
                    'bet_content' => $bet_content,
                    'current_period' => $current_period,
                    'next_periods' => $next_periods,
                    'user' => $user,
                    'popup_message' => $popup_message
                ]);
            }
            
            try {
                // Parse bet content using our new BetParser
                $parser = new BetParser();
                $parsed = $parser->parse($bet_content);
                
                Logger::log('Bet parsing result', [
                    'valid' => $parsed['valid'] ?? false,
                    'total' => $parsed['total'] ?? 0,
                    'items_count' => count($parsed['items'] ?? [])
                ]);
                
                if (!isset($parsed['valid']) || !$parsed['valid']) {
                    $error_message = isset($parsed['message']) ? $parsed['message'] : '解析下注内容失败';
                    Logger::log('Error parsing bet', ['error' => $error_message]);
                    $popup_message = ['type' => 'error', 'text' => $error_message];
                    return $this->render('order/create', [
                        'bet_content' => $bet_content,
                        'current_period' => $current_period,
                        'next_periods' => $next_periods,
                        'user' => $user,
                        'popup_message' => $popup_message
                    ]);
                }
                
                // Get current user
                $user = auth()->user();
                if (!$user) {
                    Logger::log('Error: User not authenticated');
                    $popup_message = ['type' => 'error', 'text' => '请先登录'];
                    return $this->render('order/create', [
                        'bet_content' => $bet_content,
                        'current_period' => $current_period,
                        'next_periods' => $next_periods,
                        'user' => null, // Pass null user
                        'popup_message' => $popup_message
                    ]);
                }
                
                Logger::log('User authenticated', [
                    'user_id' => $user['id'],
                    'balance' => $user['balance']
                ]);
                
                // Check if user has enough balance
                $total_amount = $parsed['total'];
                if ($confirm && $user['balance'] < $total_amount) {
                    Logger::log('Error: Insufficient balance', [
                        'user_balance' => $user['balance'],
                        'required_amount' => $total_amount
                    ]);
                    $popup_message = ['type' => 'error', 'text' => '余额不足，请充值'];
                    return $this->render('order/create', [
                        'bet_content' => $bet_content,
                        'parsed_result' => $parsed,
                        'current_period' => $current_period,
                        'next_periods' => $next_periods,
                        'user' => $user,
                        'popup_message' => $popup_message
                    ]);
                }
                
                // If confirming, create the order
                if ($confirm) {
                    Logger::log('确认下注请求', [
                        'user_id' => $user['id'],
                        'bet_content' => $bet_content,
                        'total_amount' => $parsed['total'],
                        'user_balance' => $user['balance']
                    ]);

                    // 详细记录用户数据
                    Logger::log('用户详情', [
                        'user_data_type' => gettype($user),
                        'user_keys' => is_array($user) ? array_keys($user) : 'not array',
                        'auth_check' => auth()->check(),
                        'session_user' => isset($_SESSION['user']) ? $_SESSION['user'] : 'no session user'
                    ]);
                    
                    $order = $this->createOrder($user['id'], $bet_content, $parsed);
                    
                    if ($order) {
                        Logger::log('Order created successfully', [
                            'order_id' => $order['id'],
                            'amount' => $total_amount
                        ]);
                        
                        // Set popup message instead of flash message
                        $popup_message = ['type' => 'success', 'text' => '下注成功！'];
                        
                        // Return current page with order data and popup
                        return $this->render('order/create', [
                            'bet_content' => $bet_content, // Keep original content for reference maybe?
                            'parsed_result' => $parsed,
                            'order' => $order,
                            'current_period' => $current_period,
                            'next_periods' => $next_periods,
                            'user' => $user,
                            'popup_message' => $popup_message
                        ]);
                    } else {
                        Logger::log('Error creating order', ['error' => 'Failed to create order']);
                        // 检查数据库连接是否正常
                        try {
                            $db = Database::getInstance();
                            $connected = $db && $db->getConnection() ? true : false;
                            Logger::log('数据库连接检查', ['connected' => $connected]);
                        } catch (Exception $dbEx) {
                            Logger::log('数据库连接异常', [
                                'error' => $dbEx->getMessage(),
                                'trace' => $dbEx->getTraceAsString()
                            ]);
                        }
                        
                        $popup_message = ['type' => 'error', 'text' => '创建订单失败，请检查日志了解详情'];
                        return $this->render('order/create', [
                            'bet_content' => $bet_content,
                            'current_period' => $current_period,
                            'next_periods' => $next_periods,
                            'user' => $user,
                            'popup_message' => $popup_message
                        ]);
                    }
                }
                
                // If just previewing, show the preview
                if ($preview) {
                    Logger::log('Showing bet preview', ['bet_data' => $parsed]);
                    return $this->render('order/create', [
                        'preview' => true,
                        'parsed_result' => $parsed,
                        'bet_content' => $bet_content,
                        'current_period' => $current_period,
                        'next_periods' => $next_periods,
                        'user' => $user 
                    ]);
                }
                
            } catch (Exception $e) {
                Logger::log('Exception in bet processing', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $popup_message = ['type' => 'error', 'text' => '处理下注时出错: ' . $e->getMessage()];
                return $this->render('order/create', [
                    'bet_content' => $bet_content,
                    'current_period' => $current_period,
                    'next_periods' => $next_periods,
                    'user' => $user,
                    'popup_message' => $popup_message
                ]);
            }
        } else {
            // Default content for GET request
            $bet_content = "D\n#";
        }
        
        // Load the view for GET request or if no other return happened
        return $this->render('order/create', [
            'bet_content' => $bet_content,
            'current_period' => $current_period,
            'next_periods' => $next_periods,
            'user' => $user, // Pass user data for GET request too
            'popup_message' => $popup_message // Pass null if no message set
        ]);
    }
    
    // 获取当前最近的期数
    private function getCurrentPeriod() {
        // 获取当前日期
        $today = date('Y-m-d');
        $weekday = date('N'); // 1 (周一) 到 7 (周日)
        
        // 根据当前星期计算最近的开奖日期
        if ($weekday == 7) { // 周日
            return [
                'date' => $today,
                'day' => '星期日',
                'name' => '本周日 (' . date('m-d') . ')'
            ];
        } elseif ($weekday == 6) { // 周六
            return [
                'date' => $today,
                'day' => '星期六',
                'name' => '本周六 (' . date('m-d') . ')'
            ];
        } elseif ($weekday == 3) { // 周三
            return [
                'date' => $today,
                'day' => '星期三',
                'name' => '本周三 (' . date('m-d') . ')'
            ];
        } elseif ($weekday < 3) { // 周一、周二
            // 当前周的周三
            $daysToAdd = 3 - $weekday;
            $nextDate = date('Y-m-d', strtotime("+$daysToAdd days"));
            return [
                'date' => $nextDate,
                'day' => '星期三',
                'name' => '本周三 (' . date('m-d', strtotime("+$daysToAdd days")) . ')'
            ];
        } else { // 周四、周五
            // 当前周的周六
            $daysToAdd = 6 - $weekday;
            $nextDate = date('Y-m-d', strtotime("+$daysToAdd days"));
            return [
                'date' => $nextDate,
                'day' => '星期六',
                'name' => '本周六 (' . date('m-d', strtotime("+$daysToAdd days")) . ')'
            ];
        }
    }
    
    // 获取接下来的几期
    private function getNextPeriods($count = 3) {
        $periods = [];
        $currentPeriod = $this->getCurrentPeriod();
        $startDate = $currentPeriod['date'];
        
        // 获取开奖日期 (周三、周六、周日)
        $drawDays = [3, 6, 7]; // 周三、周六、周日
        
        // 从当前日期开始，寻找未来的开奖日
        $date = new DateTime($startDate);
        $i = 0;
        
        while (count($periods) < $count) {
            $i++;
            $date->modify('+1 day');
            $weekday = (int)$date->format('N');
            
            if (in_array($weekday, $drawDays)) {
                $dayNames = ['', '周一', '周二', '周三', '周四', '周五', '周六', '周日'];
                $periods[] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => '星期' . substr($dayNames[$weekday], -1),
                    'name' => $dayNames[$weekday] . ' (' . $date->format('m-d') . ')'
                ];
            }
        }
        
        return $periods;
    }
    
    // 显示所有订单
    public function index() {
        // 获取用户角色
        $user_id = $_SESSION['user']['id'];
        $userModel = new UserModel();
        $user = $userModel->getUserById($user_id);
        
        // 根据用户角色获取不同数据
        if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
            // 管理员可以查看所有订单
            $orders = $this->orderModel->getAllOrders();
        } else {
            // 普通用户只能查看自己的订单
            $orders = $this->orderModel->getUserOrders($user_id);
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/order/index.php';
    }
    
    // 显示订单详情
    public function view($id = null) {
        if ($id === null) {
            redirect('order');
        }
        
        // 获取订单信息
        $order = $this->orderModel->getOrderById($id);
        
        if (!$order) {
            set_flash_message('error', '订单不存在');
            redirect('order');
        }
        
        // 检查权限（只能查看自己的订单，或者管理员可以查看所有订单）
        $user_id = $_SESSION['user']['id'];
        $userModel = new UserModel();
        $user = $userModel->getUserById($user_id);
        
        if ($user['role'] !== 'admin' && $user['role'] !== 'super_admin' && $order['user_id'] !== $user_id) {
            set_flash_message('error', '您没有权限查看该订单');
            redirect('order');
        }
        
        // 解析订单内容
        // $content_items = json_decode($order['content'], true); // Keep original raw content if needed
        $parsed_content = $this->betParser->parse($order['content']);
        
        // 加载视图, 传递解析后的内容
        include_once ROOT_PATH . '/views/order/view.php';
    }
    
    // 取消订单
    public function cancel($id = null) {
        if ($id === null) {
            redirect('order');
        }
        
        // 获取订单信息
        $order = $this->orderModel->getOrderById($id);
        
        if (!$order) {
            set_flash_message('error', '订单不存在');
            redirect('order');
        }
        
        // 检查权限（只能取消自己的订单，或者管理员可以取消所有订单）
        $user_id = $_SESSION['user']['id'];
        $userModel = new UserModel();
        $user = $userModel->getUserById($user_id);
        
        if ($user['role'] !== 'admin' && $user['role'] !== 'super_admin' && $order['user_id'] !== $user_id) {
            set_flash_message('error', '您没有权限取消该订单');
            redirect('order');
        }
        
        // 检查订单状态
        if ($order['status'] !== 'pending') {
            set_flash_message('error', '只能取消待处理的订单');
            redirect('order/view/' . $id);
        }
        
        // 取消订单
        $result = $this->orderModel->cancelOrder($id);
        
        if ($result) {
            set_flash_message('success', '订单已取消');
        } else {
            set_flash_message('error', '取消订单失败');
        }
        
        redirect('order/view/' . $id);
    }

    /**
     * Create a new betting order
     *
     * @param int $userId User ID
     * @param string $betContent Raw bet content
     * @param array $parsedData Parsed bet data
     * @return object|false Order object if successful, false otherwise
     */
    private function createOrder($userId, $betContent, $parsedData) {
        try {
            Logger::log('Creating new order', [
                'user_id' => $userId,
                'bet_content' => $betContent,
                'total' => $parsedData['total']
            ]);
            
            // 检查用户ID是否有效
            if (empty($userId) || !is_numeric($userId)) {
                Logger::log('无效的用户ID', ['user_id' => $userId]);
                return false;
            }
            
            // Extract lottery types from parsed data
            $lotteryTypes = isset($parsedData['lottery_types']) ? $parsedData['lottery_types'] : '';
            
            // Create order data structure expected by the OrderModel
            $orderData = [
                'user_id' => $userId,
                'content' => $betContent,
                'total_amount' => $parsedData['total'],
                'lottery_types' => $lotteryTypes
            ];
            
            // Create the order in the database
            Logger::log('Calling OrderModel->createOrder', $orderData);
            $orderId = $this->orderModel->createOrder($orderData);
            
            if (!$orderId) {
                Logger::log('Failed to create order - OrderModel returned false', ['order_data' => $orderData]);
                
                // 检查数据库连接
                try {
                    $db = Database::getInstance();
                    $stmt = $db->prepare("SELECT 1");
                    $testResult = $stmt->execute();
                    Logger::log('数据库测试查询', ['result' => $testResult ? 'succeeded' : 'failed']);
                } catch (Exception $dbEx) {
                    Logger::log('数据库测试异常', [
                        'error' => $dbEx->getMessage(),
                        'trace' => $dbEx->getTraceAsString()
                    ]);
                }
                return false;
            }
            
            Logger::log('Order created successfully', [
                'order_id' => $orderId,
                'amount' => $parsedData['total']
            ]);
            
            // Fetch the created order
            $order = $this->orderModel->getOrderById($orderId);
            
            if (!$order) {
                Logger::log('Failed to retrieve the created order', ['order_id' => $orderId]);
                return false;
            }
            
            Logger::log('Order retrieved successfully', ['order' => $order]);
            return $order;
        } catch (Exception $e) {
            Logger::log('Exception in createOrder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'data' => $parsedData
            ]);
            return false;
        }
    }

    /**
     * Render a view with data
     * 
     * @param string $view View path
     * @param array $data Data to pass to the view
     * @return void
     */
    private function render($view, $data = []) {
        // Include error and success messages if they exist
        if (isset($GLOBALS['error'])) {
            $data['error'] = $GLOBALS['error'];
        }
        
        if (isset($GLOBALS['success'])) {
            $data['success'] = $GLOBALS['success'];
        }
        
        // Extract data to make variables available in the view
        extract($data);
        
        // Include the view file
        include_once ROOT_PATH . '/views/' . $view . '.php';
    }
    
    /**
     * Set error message
     * 
     * @param string $message Error message
     * @return void
     */
    private function setError($message) {
        $GLOBALS['error'] = $message;
    }
    
    /**
     * Set success message
     * 
     * @param string $message Success message
     * @return void
     */
    private function setSuccess($message) {
        $GLOBALS['success'] = $message;
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    private function redirect($url) {
        redirect($url);
    }
} 