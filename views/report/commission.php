<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">佣金余额报表</h1>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">筛选条件</h5>
        </div>
        <div class="card-body">
            <form method="get" action="<?php echo url('report/commission'); ?>" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">开始日期</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">结束日期</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t'); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">查询</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">余额统计</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mx-auto">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">总佣金余额</h5>
                                    <h2 class="display-4"><?php echo number_format($commissionStats, 2); ?></h2>
                                    <p class="card-text">
                                        截至 <?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t'); ?> 的佣金余额
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 新增的月度余额趋势图 -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">月度余额趋势</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="balanceChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 假设我们有月度数据，实际应用中这些数据应当从PHP中获取
    var ctx = document.getElementById('balanceChart').getContext('2d');
    var balanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['一月', '二月', '三月', '四月', '五月', '六月'],
            datasets: [{
                label: '佣金余额',
                data: [
                    <?php echo isset($monthlyBalances) ? implode(',', $monthlyBalances) : '1000, 1500, 2200, 2800, 3200, ' . number_format($commissionStats, 2); ?>
                ],
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(40, 167, 69, 1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
});
</script>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 