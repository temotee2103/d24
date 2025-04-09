<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">用户报表</h1>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">用户统计</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">总用户数</h6>
                                    <h2 class="display-4"><?php echo $totalUsers; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">活跃用户</h6>
                                    <h2 class="display-4"><?php echo $activeUsers; ?></h2>
                                </div>
                                <i class="fas fa-user-check fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">今日新增</h6>
                                    <h2 class="display-4"><?php echo $newUsersToday; ?></h2>
                                </div>
                                <i class="fas fa-user-plus fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">用户增长趋势</h5>
                </div>
                <div class="card-body">
                    <canvas id="userGrowthChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">用户类型分布</h5>
                </div>
                <div class="card-body">
                    <canvas id="userTypeChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5>最活跃用户排行</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>排名</th>
                            <th>用户ID</th>
                            <th>用户名</th>
                            <th>交易数量</th>
                            <th>交易金额</th>
                            <th>最近活动</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topActiveUsers)): ?>
                            <?php $rank = 1; ?>
                            <?php foreach ($topActiveUsers as $user): ?>
                                <tr>
                                    <td><?php echo $rank++; ?></td>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['transaction_count']; ?></td>
                                    <td><?php echo number_format($user['transaction_amount'], 2); ?> 元</td>
                                    <td><?php echo $user['last_active']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">暂无数据</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 用户增长图表
    var growthCtx = document.getElementById('userGrowthChart').getContext('2d');
    var userGrowthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($userGrowthData, 'date')); ?>,
            datasets: [{
                label: '新增用户',
                data: <?php echo json_encode(array_column($userGrowthData, 'count')); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '用户数'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: '日期'
                    }
                }
            }
        }
    });
    
    // 用户类型分布图表
    var typeCtx = document.getElementById('userTypeChart').getContext('2d');
    var userTypeChart = new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($userTypeDistribution, 'role')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($userTypeDistribution, 'count')); ?>,
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
    
    // 处理时间范围变化
    document.getElementById('timeRange').addEventListener('change', function() {
        // 实际应用中应该提交表单或通过AJAX更新数据
        alert('时间范围已变更为: ' + this.value + '天，在实际应用中，此处应刷新数据。');
    });
});
</script>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 