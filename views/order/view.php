<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">订单详情</h1>
        <div>
            <?php if ($order['status'] === 'pending'): ?>
                <a href="<?php echo url('order/cancel/' . $order['id']); ?>" class="btn btn-danger" onclick="return confirm('确定要取消该订单吗？');">
                    <i class="bi bi-x-lg"></i> 取消订单
                </a>
            <?php endif; ?>
            <a href="<?php echo url('order'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> 返回订单列表
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">订单信息</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th width="30%">订单号</th>
                            <td><?php echo $order['order_number']; ?></td>
                        </tr>
                        <tr>
                            <th>用户</th>
                            <td><?php echo h($order['username']); ?></td>
                        </tr>
                        <tr>
                            <th>总金额</th>
                            <td><?php echo $order['total_amount']; ?></td>
                        </tr>
                        <tr>
                            <th>状态</th>
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
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>最后更新</th>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($order['updated_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">订单内容</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($content_items)): ?>
                        <div class="alert alert-info">订单内容为空</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>类型</th>
                                        <th>描述</th>
                                        <th>金额</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    foreach ($content_items as $item): 
                                        $total += $item['amount'];
                                    ?>
                                        <tr>
                                            <td><?php echo h($item['type']); ?></td>
                                            <td><?php echo h($item['description']); ?></td>
                                            <td><?php echo $item['amount']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-info">
                                        <th colspan="2" class="text-end">总计</th>
                                        <th><?php echo $total; ?></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 