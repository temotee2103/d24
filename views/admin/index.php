<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">管理员仪表盘</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                    <i class="fas fa-sync-alt"></i> 刷新数据
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="downloadReport">
                    <i class="fas fa-download"></i> 导出报表
                </button>
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="far fa-calendar-alt"></i> 时间范围
                </button>
                <ul class="dropdown-menu" aria-labelledby="timeRangeDropdown">
                    <li><a class="dropdown-item time-range" href="#" data-days="7">最近7天</a></li>
                    <li><a class="dropdown-item time-range" href="#" data-days="30">最近30天</a></li>
                    <li><a class="dropdown-item time-range" href="#" data-days="90">最近90天</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item time-range" href="#" data-days="0">今天</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4 text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">总用户数</h6>
                            <h2 class="display-4" id="totalUsers"><?php echo $stats['total_users']; ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                    <p class="card-text mt-2">
                        <span class="badge <?php echo $stats['user_increase'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                            <i class="fas <?php echo $stats['user_increase'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                            <?php echo abs($stats['user_increase']); ?>%
                        </span>
                        较上月
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">总销售额</h6>
                            <h2 class="display-4" id="totalSales"><?php echo number_format($stats['total_sales'], 2); ?></h2>
                        </div>
                        <i class="fas fa-coins fa-3x opacity-50"></i>
                    </div>
                    <p class="card-text mt-2">
                        <span class="badge <?php echo $stats['sales_increase'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                            <i class="fas <?php echo $stats['sales_increase'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                            <?php echo abs($stats['sales_increase']); ?>%
                        </span>
                        较上月
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">佣金支出</h6>
                            <h2 class="display-4" id="totalCommission"><?php echo number_format($stats['total_commission'], 2); ?></h2>
                        </div>
                        <i class="fas fa-hand-holding-usd fa-3x opacity-50"></i>
                    </div>
                    <p class="card-text mt-2">
                        <span class="badge <?php echo $stats['commission_increase'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                            <i class="fas <?php echo $stats['commission_increase'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                            <?php echo abs($stats['commission_increase']); ?>%
                        </span>
                        较上月
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">净利润</h6>
                            <h2 class="display-4" id="netProfit"><?php echo number_format($stats['net_profit'], 2); ?></h2>
                        </div>
                        <i class="fas fa-chart-line fa-3x opacity-50"></i>
                    </div>
                    <p class="card-text mt-2">
                        <span class="badge <?php echo $stats['profit_increase'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                            <i class="fas <?php echo $stats['profit_increase'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                            <?php echo abs($stats['profit_increase']); ?>%
                        </span>
                        较上月
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">销售趋势</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">最近订单</h5>
                    <a href="<?php echo url('order'); ?>" class="btn btn-sm btn-outline-primary">查看全部</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <div class="alert alert-info">暂无订单数据</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>订单号</th>
                                        <th>用户</th>
                                        <th>金额</th>
                                        <th>状态</th>
                                        <th>时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><?php echo $order['order_number']; ?></td>
                                            <td><?php echo h($order['username']); ?></td>
                                            <td><?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <?php
                                                    switch ($order['status']) {
                                                        case 'pending':
                                                            echo '<span class="badge bg-warning text-dark">待处理</span>';
                                                            break;
                                                        case 'completed':
                                                            echo '<span class="badge bg-success">已完成</span>';
                                                            break;
                                                        case 'cancelled':
                                                            echo '<span class="badge bg-danger">已取消</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="badge bg-secondary">未知</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo url('order/view/' . $order['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">用户分布</h5>
                </div>
                <div class="card-body">
                    <canvas id="userTypeChart" width="400" height="300"></canvas>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">最新用户</h5>
                    <a href="<?php echo url('user'); ?>" class="btn btn-sm btn-outline-primary">查看全部</a>
                </div>
                <div class="card-body">
                    <?php if (empty($new_users)): ?>
                        <div class="alert alert-info">暂无新用户</div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($new_users as $user): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo h($user['username']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></small>
                                    </div>
                                    <a href="<?php echo url('user/view/' . $user['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-user"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">系统状态</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>PHP 版本</span>
                            <span class="badge bg-primary rounded-pill"><?php echo PHP_VERSION; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>服务器时间</span>
                            <span class="badge bg-info rounded-pill"><?php echo date('Y-m-d H:i:s'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>MySQL 版本</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $server_info['mysql_version']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>内存使用</span>
                            <span class="badge bg-<?php echo $server_info['memory_usage'] < 70 ? 'success' : ($server_info['memory_usage'] < 90 ? 'warning' : 'danger'); ?> rounded-pill">
                                <?php echo $server_info['memory_usage']; ?>%
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 销售趋势图表
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($stats['sales_trend'], 'date')); ?>,
            datasets: [{
                label: '销售额',
                data: <?php echo json_encode(array_column($stats['sales_trend'], 'amount')); ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: '订单数',
                data: <?php echo json_encode(array_column($stats['sales_trend'], 'count')); ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 2,
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: '销售额'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: '订单数'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    
    // 用户类型图表
    const userTypeCtx = document.getElementById('userTypeChart').getContext('2d');
    const userTypeChart = new Chart(userTypeCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($stats['user_types'], 'type')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($stats['user_types'], 'count')); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    
    // 刷新数据按钮
    document.getElementById('refreshData').addEventListener('click', function() {
        location.reload();
    });
    
    // 时间范围选择
    document.querySelectorAll('.time-range').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const days = this.getAttribute('data-days');
            window.location.href = '<?php echo url("admin"); ?>?days=' + days;
        });
    });
    
    // 导出报表
    document.getElementById('downloadReport').addEventListener('click', function() {
        window.location.href = '<?php echo url("admin/exportReport"); ?>';
    });
});
</script>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 