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
                            <table class="table table-hover modern-table order-list-table">
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
                                            <td data-label="订单号"><span class="value"><?php echo $order['order_number']; ?></span></td>
                                            <td data-label="用户"><span class="value"><?php echo h($order['username'] ?? $_SESSION['user']['username']); ?></span></td>
                                            <td data-label="金额"><span class="value"><?php echo $order['total_amount']; ?></span></td>
                                            <td data-label="状态">
                                                <span class="value">
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
                                                </span>
                                            </td>
                                            <td data-label="创建时间"><span class="value"><?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></span></td>
                                            <td data-label="操作" class="actions-column">
                                                <a href="<?php echo url('order/view/' . $order['id']); ?>" class="btn btn-sm btn-info d-none d-md-inline-block">
                                                    <i class="bi bi-eye"></i> 查看
                                                </a>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <a href="<?php echo url('order/cancel/' . $order['id']); ?>" class="btn btn-sm btn-danger d-none d-md-inline-block" onclick="return confirm('确定要取消该订单吗？');">
                                                        <i class="bi bi-x-lg"></i> 取消
                                                    </a>
                                                <?php endif; ?>
                                                <div class="dropdown d-inline-block d-md-none">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        操作
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="<?php echo url('order/view/' . $order['id']); ?>"><i class="bi bi-eye me-2"></i>查看</a></li>
                                                        <?php if ($order['status'] === 'pending'): ?>
                                                            <li><a class="dropdown-item text-danger" href="<?php echo url('order/cancel/' . $order['id']); ?>" onclick="return confirm('确定要取消该订单吗？');"><i class="bi bi-x-lg me-2"></i>取消订单</a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
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