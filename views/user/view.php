<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">代理详情</h1>
        <div>
            <a href="<?php echo url('user/edit/' . $viewUser['id']); ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> 编辑
            </a>
            <a href="<?php echo url('user/deposit/' . $viewUser['id']); ?>" class="btn btn-success">
                <i class="bi bi-cash"></i> <?php echo $_SESSION['user']['role'] === 'super_admin' ? '充值' : '转账'; ?>
            </a>
            <?php if ($_SESSION['user']['role'] === 'super_admin' && $viewUser['id'] != $_SESSION['user']['id']): ?>
            <a href="<?php echo url('user/delete/' . $viewUser['id']); ?>" 
               class="btn btn-danger" 
               onclick="return confirm('确定要删除用户 <?php echo h($viewUser['username']); ?> 吗？此操作不可恢复！');">
                <i class="bi bi-trash"></i> 删除
            </a>
            <?php endif; ?>
            <a href="<?php echo url('user/subagents'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> 返回代理列表
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="modern-card mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle me-3" style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h5 class="card-title mb-0">基本信息</h5>
                </div>
                <table class="table modern-table">
                    <tr>
                        <th width="30%">用户名</th>
                        <td><?php echo h($viewUser['username']); ?></td>
                    </tr>
                    <tr>
                        <th>昵称</th>
                        <td><?php echo h($viewUser['nickname']); ?></td>
                    </tr>
                    <tr>
                        <th>手机号码</th>
                        <td><?php echo h($viewUser['phone']); ?></td>
                    </tr>
                    <tr>
                        <th>用户级别</th>
                        <td>
                            <?php 
                                switch ($viewUser['role']) {
                                    case 'super_admin':
                                        echo '超级管理员';
                                        break;
                                    case 'admin':
                                        echo '管理员';
                                        break;
                                    default:
                                        echo '代理';
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>状态</th>
                        <td><?php echo $viewUser['status'] === 'active' ? '<span class="badge bg-success">激活</span>' : '<span class="badge bg-danger">停用</span>'; ?></td>
                    </tr>
                    <tr>
                        <th>佣金比例</th>
                        <td><?php echo $viewUser['commission_rate']; ?>%</td>
                    </tr>
                    <tr>
                        <th>可开下线</th>
                        <td><?php echo $viewUser['can_create_subagent'] ? '<span class="badge bg-success">可以</span>' : '<span class="badge bg-secondary">不可以</span>'; ?></td>
                    </tr>
                    <tr>
                        <th>备注</th>
                        <td><?php echo h($viewUser['notes']); ?></td>
                    </tr>
                    <tr>
                        <th>创建时间</th>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($viewUser['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="modern-card mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle me-3" style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #10B981 0%, #3B82F6 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h5 class="card-title mb-0">账户信息</h5>
                </div>
                <table class="table modern-table">
                    <tr>
                        <th width="30%">账户余额</th>
                        <td><?php echo number_format($viewUser['balance'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>下线代理数</th>
                        <td><?php echo $subagentCount; ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="modern-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle me-3" style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #F43F5E 0%, #EC4899 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <h5 class="card-title mb-0">最近订单</h5>
                </div>
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">暂无订单记录</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover modern-table">
                            <thead>
                                <tr>
                                    <th>订单号</th>
                                    <th>金额</th>
                                    <th>状态</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $count = 0;
                                foreach ($orders as $order): 
                                    if ($count >= 5) break; // 只显示最近5条
                                    $count++;
                                ?>
                                    <tr>
                                        <td><a href="<?php echo url('order/view/' . $order['id']); ?>"><?php echo $order['order_number']; ?></a></td>
                                        <td><?php echo $order['total_amount']; ?></td>
                                        <td>
                                            <?php 
                                                switch ($order['status']) {
                                                    case 'completed':
                                                        echo '<span class="badge bg-success">已完成</span>';
                                                        break;
                                                    case 'pending':
                                                        echo '<span class="badge bg-warning">处理中</span>';
                                                        break;
                                                    case 'cancelled':
                                                        echo '<span class="badge bg-danger">已取消</span>';
                                                        break;
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($orders) > 5): ?>
                        <div class="mt-2 text-center">
                            <a href="<?php echo url('order'); ?>" class="btn btn-sm btn-outline-primary">查看更多订单</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 