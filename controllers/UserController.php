<?php
class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
        AuthController::requireLogin();
    }
    
    // 显示所有用户
    public function index() {
        // 检查是否是管理员
        AuthController::requireAdmin();
        
        // 获取所有用户
        $users = $this->userModel->getAllUsers();
        
        // 加载视图
        include_once ROOT_PATH . '/views/user/index.php';
    }
    
    // 显示下线用户
    public function subagents() {
        $user_id = $_SESSION['user']['id'];
        $user_role = $_SESSION['user']['role'] ?? '';
        
        // 处理排序
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
        $order = isset($_GET['order']) ? $_GET['order'] : 'asc';
        
        // 验证排序字段和顺序
        $validSortFields = ['id', 'username', 'balance', 'created_at'];
        $validOrders = ['asc', 'desc'];
        
        if (!in_array($sort, $validSortFields)) {
            $sort = 'id';
        }
        
        if (!in_array($order, $validOrders)) {
            $order = 'asc';
        }
        
        // 如果是超级管理员，获取所有用户（排除自己）
        if ($user_role === 'super_admin') {
            $subagents = $this->userModel->getAllUsersExceptCurrent($user_id, $sort, $order);
        } else {
            // 获取当前用户的下级代理
            $subagents = $this->userModel->getSubagents($user_id, $sort, $order);
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/user/subagents.php';
    }
    
    // 显示创建用户表单
    public function create() {
        // 检查用户是否有权限开下线
        $user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0;
        $user = $this->userModel->getUserById($user_id);
        
        if (!$user || ($user['can_create_subagent'] == 0 && $user['role'] !== 'super_admin')) {
            set_flash_message('error', '您没有权限创建下级代理');
            redirect('user/subagents');
        }
        
        // 确保session中有佣金比例信息
        if (isset($user['commission_rate'])) {
            $_SESSION['user']['commission_rate'] = $user['commission_rate']; 
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 获取表单数据
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $nickname = $_POST['nickname'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $commission_rate = isset($_POST['commission_rate']) ? (float)$_POST['commission_rate'] : 0;
            $can_create_subagent = isset($_POST['can_create_subagent']) ? 1 : 0;
            
            // 超级管理员可以选择角色，其他用户只能创建代理
            $role = ($user['role'] === 'super_admin' && isset($_POST['role'])) ? $_POST['role'] : 'agent';
            
            // 简单验证
            if (empty($username) || empty($password)) {
                $error = '用户名和密码不能为空';
            } else if ($this->userModel->getUserByUsername($username)) {
                $error = '用户名已存在';
            } else {
                // 检查佣金比例是否合法（超级管理员不受限制）
                if ($user['role'] !== 'super_admin' && ($commission_rate < 0 || $commission_rate > $user['commission_rate'])) {
                    $error = '佣金比例不能大于您的佣金比例（' . $user['commission_rate'] . '%）';
                } else {
                    // 创建用户
                    $userData = [
                        'username' => $username,
                        'password' => $password,
                        'nickname' => $nickname,
                        'phone' => $phone,
                        'notes' => $notes,
                        'role' => $role,
                        'commission_rate' => $commission_rate,
                        'can_create_subagent' => $can_create_subagent,
                        'parent_id' => $user_id
                    ];
                    
                    $result = $this->userModel->createUser($userData);
                    
                    if ($result) {
                        $success = '用户创建成功';
                    } else {
                        $error = '创建失败，请稍后再试';
                    }
                }
            }
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/user/create.php';
    }
    
    // 显示编辑用户表单
    public function edit($id = null) {
        if ($id === null) {
            redirect('user/subagents');
        }
        
        // 获取用户信息
        $editUser = $this->userModel->getUserById($id);
        
        if (!$editUser) {
            set_flash_message('error', '用户不存在');
            redirect('user/subagents');
        }
        
        // 检查权限（只能编辑自己的下级代理）
        $user_id = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($user_id);
        
        // 如果不是管理员，且不是自己的下级，则无权编辑
        if ($user['role'] !== 'admin' && $user['role'] !== 'super_admin' && $editUser['parent_id'] !== $user_id) {
            set_flash_message('error', '您没有权限编辑该用户');
            redirect('user/subagents');
        }
        
        // 如果是超级管理员，获取所有可能的上线用户
        $allUsers = [];
        if ($user['role'] === 'super_admin') {
            $allUsers = $this->userModel->getAllUsers();
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 获取表单数据
            $password = $_POST['password'] ?? '';
            $nickname = $_POST['nickname'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $commission_rate = isset($_POST['commission_rate']) ? (float)$_POST['commission_rate'] : 0;
            $can_create_subagent = isset($_POST['can_create_subagent']) ? 1 : 0;
            $status = $_POST['status'] ?? 'active';
            
            // 超级管理员可以更改角色和上线
            $role = $editUser['role']; // 默认保持原角色
            $parent_id = $editUser['parent_id']; // 默认保持原上线
            
            if ($user['role'] === 'super_admin') {
                if (isset($_POST['role']) && $_POST['role'] !== 'super_admin') {
                    $role = $_POST['role'];
                }
                
                if (isset($_POST['parent_id'])) {
                    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
                }
            }
            
            // 检查佣金比例是否合法（超级管理员不受限制）
            if ($user['role'] !== 'super_admin' && ($commission_rate < 0 || $commission_rate > $user['commission_rate'])) {
                $error = '佣金比例不能大于您的佣金比例（' . $user['commission_rate'] . '%）';
            } else {
                // 更新用户数据
                $userData = [
                    'nickname' => $nickname,
                    'phone' => $phone,
                    'notes' => $notes,
                    'commission_rate' => $commission_rate,
                    'can_create_subagent' => $can_create_subagent,
                    'status' => $status,
                    'role' => $role,
                    'parent_id' => $parent_id
                ];
                
                // 如果输入了密码，则更新密码
                if (!empty($password)) {
                    $userData['password'] = $password;
                }
                
                $result = $this->userModel->updateUser($id, $userData);
                
                if ($result) {
                    $success = '用户信息更新成功';
                    $editUser = $this->userModel->getUserById($id); // 重新获取用户信息
                } else {
                    $error = '更新失败，请稍后再试';
                }
            }
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/user/edit.php';
    }
    
    // 显示用户详细信息
    public function view($id = null) {
        if ($id === null) {
            redirect('user/subagents');
        }
        
        // 获取用户信息
        $viewUser = $this->userModel->getUserById($id);
        
        if (!$viewUser) {
            set_flash_message('error', '用户不存在');
            redirect('user/subagents');
        }
        
        // 获取下线代理总数
        $subagentCount = $this->userModel->countSubagents($id);
        
        // 获取订单信息
        $orderModel = new OrderModel();
        $orders = $orderModel->getUserOrders($id);
        
        // 加载视图
        include_once ROOT_PATH . '/views/user/view.php';
    }
    
    // 处理用户充值
    public function deposit($id = null) {
        if ($id === null) {
            redirect('user/subagents');
        }
        
        // 获取用户信息
        $depositUser = $this->userModel->getUserById($id);
        
        if (!$depositUser) {
            set_flash_message('error', '用户不存在');
            redirect('user/subagents');
        }
        
        // 检查权限（只能给自己的下级代理充值，或者管理员给任何用户充值）
        $user_id = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($user_id);
        
        // 如果不是管理员，且不是自己的下级，则无权充值
        if ($user['role'] !== 'admin' && $user['role'] !== 'super_admin' && $depositUser['parent_id'] !== $user_id) {
            set_flash_message('error', '您没有权限给该用户充值');
            redirect('user/subagents');
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 获取表单数据
            $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
            
            if ($amount <= 0) {
                $error = '充值金额必须大于0';
            } else {
                // 检查当前用户是否有足够的余额进行充值（超级管理员除外）
                if ($user['role'] !== 'super_admin' && $user['balance'] < $amount) {
                    $error = '您的余额不足，无法完成充值。当前余额: ' . number_format($user['balance'], 2);
                } else {
                    try {
                        $db = Database::getInstance();
                        // 创建交易记录模型（在开始事务前创建）
                        $transactionModel = new TransactionModel();
                        
                        // 只有非超级管理员需要事务处理（因为涉及到两个账户操作）
                        if ($user['role'] !== 'super_admin') {
                            $db->beginTransaction();
                            
                            // 先扣除当前用户的余额
                            $sourceNewBalance = $this->userModel->updateBalance($user_id, $amount, 'subtract');
                            
                            if ($sourceNewBalance === false) {
                                $db->rollBack();
                                $error = '从您的账户扣除余额失败';
                                include_once ROOT_PATH . '/views/user/deposit.php';
                                return;
                            }
                            
                            // 记录转出交易
                            $transactionData = [
                                'user_id' => $user_id,
                                'type' => 'transfer_out',
                                'amount' => $amount,
                                'balance_before' => $sourceNewBalance + $amount,
                                'balance_after' => $sourceNewBalance,
                                'notes' => '转账给 ' . $depositUser['username']
                            ];
                            $transactionModel->createTransaction($transactionData);
                        }
                        
                        // 更新目标用户余额
                        $targetNewBalance = $this->userModel->updateBalance($id, $amount, 'add');
                        
                        if ($targetNewBalance !== false) {
                            // 创建交易记录
                            $transactionData = [
                                'user_id' => $id,
                                'type' => $user['role'] === 'super_admin' ? 'deposit' : 'transfer_in',
                                'amount' => $amount,
                                'balance_before' => $targetNewBalance - $amount,
                                'balance_after' => $targetNewBalance,
                                'notes' => '由 ' . $user['username'] . ' ' . ($user['role'] === 'super_admin' ? '充值' : '转入')
                            ];
                            
                            $transactionModel->createTransaction($transactionData);
                            
                            // 只有在开启了事务的情况下才提交
                            if ($user['role'] !== 'super_admin') {
                                $db->commit();
                            }
                            
                            $success = ($user['role'] === 'super_admin' ? '充值' : '转账') . '成功，已增加 ' . $amount . ' 余额';
                            $depositUser = $this->userModel->getUserById($id); // 重新获取用户信息
                            
                            // 如果不是超级管理员，更新当前用户的session信息
                            if ($user['role'] !== 'super_admin') {
                                $_SESSION['user'] = $this->userModel->getUserById($user_id);
                            }
                        } else {
                            // 只有在开启了事务的情况下才回滚
                            if ($user['role'] !== 'super_admin') {
                                $db->rollBack();
                            }
                            $error = '充值失败，请稍后再试';
                        }
                    } catch (Exception $e) {
                        // 只有在开启了事务的情况下才回滚
                        if ($user['role'] !== 'super_admin') {
                            $db = Database::getInstance();
                            $db->rollBack();
                        }
                        $error = '充值过程中发生错误：' . $e->getMessage();
                    }
                }
            }
        }
        
        // 加载视图
        include_once ROOT_PATH . '/views/user/deposit.php';
    }
    
    // 将所有用户的信用额度转换为余额
    public function migrateCredit() {
        // 检查权限，只有超级管理员可以执行
        if ($_SESSION['user']['role'] !== 'super_admin') {
            set_flash_message('error', '您没有权限执行此操作');
            redirect('home');
            return;
        }
        
        $migrated_count = $this->userModel->migrateAllCreditToBalance();
        
        if ($migrated_count !== false) {
            set_flash_message('success', '成功将 ' . $migrated_count . ' 名用户的信用额度转换为余额');
        } else {
            set_flash_message('error', '转换过程中发生错误');
        }
        
        redirect('user/subagents');
    }
    
    // 检查余额页面
    public function check_balance() {
        // 获取当前用户信息
        $user_id = $_SESSION['user']['id'];
        
        // 从数据库获取用户信息
        $dbUser = $this->userModel->getUserById($user_id);
        
        // 获取最近的交易记录
        $transactionModel = new TransactionModel();
        $transactions = $transactionModel->getUserTransactions($user_id);
        $transactions = array_slice($transactions, 0, 5); // 只取最近5条
        
        $lastTransaction = !empty($transactions) ? $transactions[0] : null;
        
        // 加载视图
        include_once ROOT_PATH . '/views/user/balance_check.php';
    }
    
    // 同步用户余额
    public function sync_balance() {
        // 获取当前用户信息
        $user_id = $_SESSION['user']['id'];
        
        // 从数据库获取用户信息
        $dbUser = $this->userModel->getUserById($user_id);
        
        if ($dbUser) {
            // 更新session中的用户信息
            $_SESSION['user'] = $dbUser;
            
            // 记录同步操作
            $this->userModel->logUserActivity($user_id, 'sync', '余额同步');
            
            set_flash_message('success', '余额已成功同步');
        } else {
            set_flash_message('error', '同步失败，无法获取用户信息');
        }
        
        // 重定向到余额检查页面
        redirect('user/check_balance');
    }
    
    // 删除用户（仅超级管理员可用）
    public function delete($id = null) {
        // 检查是否是超级管理员
        if ($_SESSION['user']['role'] !== 'super_admin') {
            set_flash_message('error', '只有超级管理员可以删除用户');
            redirect('user/subagents');
            return;
        }
        
        if ($id === null) {
            set_flash_message('error', '未指定要删除的用户');
            redirect('user/subagents');
            return;
        }
        
        // 确保不删除自己
        if ($id == $_SESSION['user']['id']) {
            set_flash_message('error', '不能删除当前登录的账户');
            redirect('user/subagents');
            return;
        }
        
        // 获取用户信息
        $user = $this->userModel->getUserById($id);
        
        if (!$user) {
            set_flash_message('error', '用户不存在');
            redirect('user/subagents');
            return;
        }
        
        // 检查是否有下级代理
        $subagentCount = $this->userModel->countSubagents($id);
        if ($subagentCount > 0) {
            set_flash_message('error', '无法删除有下级代理的用户，请先删除或转移其下级代理');
            redirect('user/view/' . $id);
            return;
        }
        
        // 执行删除
        if ($this->userModel->deleteUser($id)) {
            set_flash_message('success', '用户 ' . $user['username'] . ' 已成功删除');
        } else {
            set_flash_message('error', '删除用户失败，请稍后再试');
        }
        
        redirect('user/subagents');
    }
    
    // 处理批量操作
    public function batch_action() {
        // 检查是否是超级管理员
        if ($_SESSION['user']['role'] !== 'super_admin') {
            set_flash_message('error', '只有超级管理员可以执行批量操作');
            redirect('user/subagents');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('user/subagents');
            return;
        }
        
        $action = $_POST['batch_action'] ?? '';
        $selected_users = $_POST['selected_users'] ?? [];
        
        if (empty($action) || empty($selected_users)) {
            set_flash_message('error', '请选择操作和用户');
            redirect('user/subagents');
            return;
        }
        
        $current_user_id = $_SESSION['user']['id'];
        $success_count = 0;
        $error_count = 0;
        
        // 从选中用户中移除当前用户（防止自己操作自己）
        $selected_users = array_filter($selected_users, function($id) use ($current_user_id) {
            return $id != $current_user_id;
        });
        
        foreach ($selected_users as $user_id) {
            switch ($action) {
                case 'activate':
                    $userData = ['status' => 'active'];
                    if ($this->userModel->updateUser($user_id, $userData)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                    break;
                
                case 'deactivate':
                    $userData = ['status' => 'inactive'];
                    if ($this->userModel->updateUser($user_id, $userData)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                    break;
                
                case 'delete':
                    // 检查是否有下级代理
                    $subagentCount = $this->userModel->countSubagents($user_id);
                    if ($subagentCount > 0) {
                        $error_count++;
                        continue 2; // 修改为continue 2，指向外部的foreach循环
                    }
                    
                    if ($this->userModel->deleteUser($user_id)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                    break;
            }
        }
        
        if ($success_count > 0) {
            set_flash_message('success', "成功处理了 {$success_count} 个用户");
        }
        
        if ($error_count > 0) {
            set_flash_message('error', "有 {$error_count} 个用户处理失败");
        }
        
        redirect('user/subagents');
    }
    
    // 切换用户状态
    public function toggle_status($id = null) {
        // 检查是否是超级管理员
        if ($_SESSION['user']['role'] !== 'super_admin') {
            set_flash_message('error', '只有超级管理员可以更改用户状态');
            redirect('user/subagents');
            return;
        }
        
        if ($id === null) {
            set_flash_message('error', '未指定用户');
            redirect('user/subagents');
            return;
        }
        
        // 获取用户信息
        $user = $this->userModel->getUserById($id);
        
        if (!$user) {
            set_flash_message('error', '用户不存在');
            redirect('user/subagents');
            return;
        }
        
        // 确保不操作自己
        if ($id == $_SESSION['user']['id']) {
            set_flash_message('error', '不能更改当前登录账户的状态');
            redirect('user/subagents');
            return;
        }
        
        // 切换状态
        $new_status = $user['status'] === 'active' ? 'inactive' : 'active';
        
        $userData = ['status' => $new_status];
        if ($this->userModel->updateUser($id, $userData)) {
            set_flash_message('success', '用户状态已更改为 ' . ($new_status === 'active' ? '激活' : '停用'));
        } else {
            set_flash_message('error', '状态更改失败');
        }
        
        redirect('user/subagents');
    }
} 