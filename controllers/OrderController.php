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
    private $periodModel;
    private $logger;
    
    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->betParser = new BetParser();
        $this->periodModel = new Period();
        $this->logger = new Logger('OrderController');
        AuthController::requireLogin();
    }
    
    /**
     * 创建订单
     */
    public function create() {
        // 检查是否为POST请求
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $currentPeriod = $this->periodModel->getCurrentPeriod();
            include_once 'views/order/create.php';
            return;
        }

        // 获取当前用户ID
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        
        // 获取当前期数
        $period = $this->periodModel->getCurrentPeriod();
        if (!$period) {
            $_SESSION['error'] = '当前没有开放的期数';
            header('Location: ?controller=order&action=create');
            exit;
        }
        
        $this->logger->debug("处理订单创建请求: period_id={$period['id']}, user_id=$user_id");
        
        // 获取下注内容
        $bet_content = isset($_POST['bet_content']) ? trim($_POST['bet_content']) : '';
        if (empty($bet_content)) {
            $this->logger->error("下注内容为空");
            $_SESSION['error'] = '下注内容不能为空';
            header('Location: ?controller=order&action=create');
            exit;
        }
        
        $this->logger->debug("原始下注内容: $bet_content");
        
        // 解析下注内容
        $betParser = new BetParser();
        try {
            $this->logger->debug("开始解析下注内容");
            $parsedData = $betParser->parse($bet_content);
            
            if (!$parsedData['success']) {
                $this->logger->error("解析下注内容失败: " . $parsedData['message']);
                $_SESSION['error'] = '下注内容格式错误: ' . $parsedData['message'];
                header('Location: ?controller=order&action=create');
                exit;
            }
            
            $this->logger->debug("解析结果: " . print_r($parsedData, true));
            
            // 创建订单
            $result = $this->orderModel->create(
                $user_id, 
                $period['id'], 
                $bet_content, 
                $parsedData['total_amount'], 
                $parsedData['items']
            );
            
            if ($result['success']) {
                $this->logger->debug("订单创建成功: order_id=" . $result['order_id']);
                $_SESSION['success'] = '下注成功，订单号: ' . $result['order_id'];
                header('Location: ?controller=order&action=view&id=' . $result['order_id']);
                exit;
            } else {
                $this->logger->error("订单创建失败: " . $result['message']);
                $_SESSION['error'] = '下注失败: ' . $result['message'];
                header('Location: ?controller=order&action=create');
                exit;
            }
        } catch (Exception $e) {
            $this->logger->error("处理订单时发生异常: " . $e->getMessage());
            $this->logger->error("异常堆栈: " . $e->getTraceAsString());
            $_SESSION['error'] = '系统错误，请稍后再试';
            header('Location: ?controller=order&action=create');
            exit;
        }
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
        $content_items = json_decode($order['content'], true);
        
        // 加载视图
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