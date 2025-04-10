<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">订单管理</h1>
        <a href="<?php echo url('order/create'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> 新建订单
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($orders)): ?>
                <div class="alert alert-info">
                    暂无订单记录。<a href="<?php echo url('order/create'); ?>">点击此处创建新订单</a>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover modern-table">
                                <thead>
                                    <tr>
                                        <th>订单号</th>
                                        <th>用户</th>
                                        <th>金额</th>
                                        <th>状态</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?php echo $order['order_number']; ?></td>
                                            <td><?php echo h($order['username'] ?? $_SESSION['user']['username']); ?></td>
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
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo url('order/view/' . $order['id']); ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> 查看
                                                </a>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <a href="<?php echo url('order/cancel/' . $order['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要取消该订单吗？');">
                                                        <i class="bi bi-x-lg"></i> 取消
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?>