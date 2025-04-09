<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">销售报表</h1>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">筛选条件</h5>
        </div>
        <div class="card-body">
            <form method="get" action="<?php echo url('report/sales'); ?>" class="row g-3">
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
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">销售统计</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">总销售额</h5>
                                    <h2 class="display-6"><?php echo number_format($salesStats['total_sales'], 2); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">总佣金</h5>
                                    <h2 class="display-6"><?php echo number_format($salesStats['total_commission'], 2); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">净销售额</h5>
                                    <h2 class="display-6"><?php echo number_format($salesStats['net_sales'], 2); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h4>报表说明</h4>
                        <p>此报表显示从 <?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?> 到 <?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t'); ?> 期间的销售统计。</p>
                        <ul>
                            <li><strong>总销售额</strong>：所有用户在此期间的下注总额</li>
                            <li><strong>总佣金</strong>：支付给所有代理的佣金总额</li>
                            <li><strong>净销售额</strong>：总销售额减去佣金后的金额（需要上交的金额）</li>
                        </ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <canvas id="salesChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 创建销售图表
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['总销售额', '总佣金', '净销售额'],
            datasets: [{
                label: '金额',
                data: [
                    <?php echo $salesStats['total_sales']; ?>,
                    <?php echo $salesStats['total_commission']; ?>,
                    <?php echo $salesStats['net_sales']; ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 