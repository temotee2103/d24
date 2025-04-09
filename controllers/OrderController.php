<?php
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
        $error = '';
        $success = '';
        $parsed_result = null;
        $bet_content = '';
        
        // 获取当前期数信息
        $current_period = $this->getCurrentPeriod();
        $next_periods = $this->getNextPeriods(3);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bet_content = $_POST['bet_content'] ?? '';
            
            if (empty($bet_content)) {
                $error = '下注内容不能为空';
            } else {
                // 解析下注内容
                $parsed_result = $this->betParser->parse($bet_content);
                
                if (!$parsed_result['valid']) {
                    $error = '下注格式错误: ' . $parsed_result['error'];
                } else {
                    $user_id = $_SESSION['user']['id'];
                    $userModel = new UserModel();
                    $user = $userModel->getUserById($user_id);
                    
                    // 检查余额是否足够
                    if ($user['balance'] < $parsed_result['total']) {
                        $error = '余额不足，当前余额: ' . $user['balance'] . '，需要: ' . $parsed_result['total'];
                    } else {
                        // 如果是确认提交
                        if (isset($_POST['confirm'])) {
                            // 获取彩种类型
                            $lottery_types = [];
                            foreach ($parsed_result['items'] as $item) {
                                if (isset($item['lottery_type'])) {
                                    $lottery_types = array_merge($lottery_types, $item['lottery_type']);
                                }
                            }
                            $lottery_types = array_unique($lottery_types);
                            
                            // 创建订单
                            $order_data = [
                                'user_id' => $user_id,
                                'content' => json_encode($parsed_result['items']),
                                'total_amount' => $parsed_result['total'],
                                'lottery_types' => implode('', $lottery_types)
                            ];
                            
                            $order_id = $this->orderModel->createOrder($order_data);
                            
                            if ($order_id) {
                                // 计算佣金
                                $this->orderModel->calculateCommission($user_id, $parsed_result['total']);
                                
                                $success = '下注成功，订单号: ' . $order_id;
                                // 不重置bet_content，保持当前内容以展示收据
                            } else {
                                $error = '下注失败，请稍后再试';
                                $parsed_result = null; // 清空解析结果
                            }
                        }
                        // 如果是预览，不执行下注，只显示解析结果（默认情况）
                    }
                }
            }
        } else {
            // GET请求，设置默认值
            $bet_content = 'D
#';
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/order/create.php';
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
} 