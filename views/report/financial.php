<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>
<?php include_once ROOT_PATH . '/includes/transaction_helpers.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">综合财务报表</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> 打印
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportTableToExcel('financialTable', 'financial_report')">
                <i class="bi bi-file-earmark-excel"></i> 导出Excel
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportChart()">
                <i class="bi bi-file-earmark-image"></i> 导出图表
            </button>
        </div>
    </div>
    
    <!-- 筛选条件 -->
    <div class="modern-card mb-4">
        <h5 class="mb-3">筛选条件</h5>
        <form method="get" action="<?php echo url('report/financial'); ?>" class="row g-3">
            <div class="col-md-4">
                <label for="start_date" class="form-label">开始日期</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">结束日期</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t'); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-gradient-primary">查询</button>
            </div>
        </form>
    </div>
    
    <!-- 财务概览 -->
    <div class="row">
        <!-- 总营业额卡片 -->
        <div class="col-md-3 mb-4">
            <div class="modern-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">总营业额</h5>
                        <h2 class="display-5 text-primary fw-bold">RM<?php echo number_format($financialStats['total_sales'], 2); ?></h2>
                    </div>
                    <div class="icon-box" style="width: 60px; height: 60px; border-radius: 12px; background: rgba(99, 102, 241, 0.1); display: flex; align-items: center; justify-content: center; color: #6366F1; font-size: 1.8rem;">
                        <i class="bi bi-cart"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 总佣金卡片 -->
        <div class="col-md-3 mb-4">
            <div class="modern-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">总佣金</h5>
                        <h2 class="display-5 text-warning fw-bold">RM<?php echo number_format($financialStats['total_commission'], 2); ?></h2>
                    </div>
                    <div class="icon-box" style="width: 60px; height: 60px; border-radius: 12px; background: rgba(255, 193, 7, 0.1); display: flex; align-items: center; justify-content: center; color: #ffc107; font-size: 1.8rem;">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 净利润卡片 -->
        <div class="col-md-3 mb-4">
            <div class="modern-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">利润</h5>
                        <h2 class="display-5 text-success fw-bold">RM<?php echo number_format($financialStats['net_profit'], 2); ?></h2>
                    </div>
                    <div class="icon-box" style="width: 60px; height: 60px; border-radius: 12px; background: rgba(40, 167, 69, 0.1); display: flex; align-items: center; justify-content: center; color: #28a745; font-size: 1.8rem;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 利润率卡片 -->
        <div class="col-md-3 mb-4">
            <div class="modern-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">利润率</h5>
                        <h2 class="display-5 text-info fw-bold"><?php echo number_format($financialStats['profit_margin'], 2); ?>%</h2>
                    </div>
                    <div class="icon-box" style="width: 60px; height: 60px; border-radius: 12px; background: rgba(23, 162, 184, 0.1); display: flex; align-items: center; justify-content: center; color: #17a2b8; font-size: 1.8rem;">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 图表 -->
    <div class="modern-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">财务走势分析</h5>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleChartType('line')">
                    <i class="bi bi-graph-up"></i> 折线图
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleChartType('bar')">
                    <i class="bi bi-bar-chart"></i> 柱状图
                </button>
            </div>
        </div>
        <div>
            <canvas id="financialChart" height="300"></canvas>
        </div>
    </div>
    
    <!-- 财务交易明细表格 -->
    <div class="modern-card">
        <div class="table-header">
            <div>
                <h5 class="mb-0">财务交易明细</h5>
                <p class="text-muted small mb-0 mt-1">
                    <?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?> 至 
                    <?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t'); ?>
                </p>
            </div>
        </div>
        
        <div class="table-responsive">
            <?php if (isset($financialRecords) && !empty($financialRecords)): ?>
                <table class="table table-hover modern-table" id="financialTable">
                    <thead>
                        <tr>
                            <th class="text-center">订单号</th>
                            <th>用户</th>
                            <th class="text-end">营业额</th>
                            <th class="text-end">佣金支出</th>
                            <th class="text-end">净利润</th>
                            <th class="text-end">利润率</th>
                            <th class="text-center">时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financialRecords as $record): ?>
                            <tr>
                                <td class="text-center"><?= $record['order_number'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; background-color: #f8f9fa;">
                                            <i class="bi bi-person text-muted"></i>
                                        </div>
                                        <?= htmlspecialchars($record['username']) ?>
                                    </div>
                                </td>
                                <td class="text-end fw-medium">RM<?= number_format($record['amount'], 2) ?></td>
                                <td class="text-end fw-medium">RM<?= number_format($record['commission'], 2) ?></td>
                                <td class="text-end fw-medium">
                                    <span class="<?= $record['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        RM<?= number_format($record['profit'], 2) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-medium"><?= number_format($record['profit_margin'], 2) ?>%</td>
                                <td class="text-center small"><?= $record['created_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">合计:</td>
                            <td class="text-end">RM<?= number_format($financialStats['total_sales'], 2) ?></td>
                            <td class="text-end">RM<?= number_format($financialStats['total_commission'], 2) ?></td>
                            <td class="text-end">RM<?= number_format($financialStats['net_profit'], 2) ?></td>
                            <td class="text-end"><?= number_format($financialStats['profit_margin'], 2) ?>%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <div class="table-empty-state">
                    <i class="bi bi-info-circle"></i>
                    没有找到财务交易记录。请调整筛选条件重试。
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let financialChart;
let chartType = 'line';

document.addEventListener('DOMContentLoaded', function() {
    // 每日数据
    const dates = [<?php echo isset($chartData['dates']) ? $chartData['dates'] : "'1日', '2日', '3日', '4日', '5日', '6日', '7日'" ?>];
    const salesData = [<?php echo isset($chartData['sales']) ? implode(',', $chartData['sales']) : "5000, 6200, 4800, 7500, 8900, 7600, 9200" ?>];
    const commissionData = [<?php echo isset($chartData['commission']) ? implode(',', $chartData['commission']) : "1000, 1240, 960, 1500, 1780, 1520, 1840" ?>];
    const profitData = [<?php echo isset($chartData['profit']) ? implode(',', $chartData['profit']) : "4000, 4960, 3840, 6000, 7120, 6080, 7360" ?>];
    
    // 创建图表
    createChart(chartType, dates, salesData, commissionData, profitData);
});

function createChart(type, labels, salesData, commissionData, profitData) {
    var ctx = document.getElementById('financialChart').getContext('2d');
    
    // 销毁现有图表（如果存在）
    if (financialChart) {
        financialChart.destroy();
    }
    
    financialChart = new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: [
                {
                    label: '营业额',
                    data: salesData,
                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                    tension: 0.4
                },
                {
                    label: '佣金支出',
                    data: commissionData,
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(255, 193, 7, 1)',
                    tension: 0.4
                },
                {
                    label: '净利润',
                    data: profitData,
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(40, 167, 69, 1)',
                    tension: 0.4
                }
            ]
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': RM';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}

function toggleChartType(type) {
    chartType = type;
    
    // 重新获取数据并创建图表
    const dates = [<?php echo isset($chartData['dates']) ? $chartData['dates'] : "'1日', '2日', '3日', '4日', '5日', '6日', '7日'" ?>];
    const salesData = [<?php echo isset($chartData['sales']) ? implode(',', $chartData['sales']) : "5000, 6200, 4800, 7500, 8900, 7600, 9200" ?>];
    const commissionData = [<?php echo isset($chartData['commission']) ? implode(',', $chartData['commission']) : "1000, 1240, 960, 1500, 1780, 1520, 1840" ?>];
    const profitData = [<?php echo isset($chartData['profit']) ? implode(',', $chartData['profit']) : "4000, 4960, 3840, 6000, 7120, 6080, 7360" ?>];
    
    createChart(type, dates, salesData, commissionData, profitData);
}

function exportChart() {
    // 创建一个临时链接元素
    var link = document.createElement('a');
    link.href = financialChart.toBase64Image();
    link.download = '财务报表_<?php echo date('Ymd'); ?>.png';
    link.click();
}

// 导出表格到Excel函数
function exportTableToExcel(tableID, filename = '') {
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    // 指定文件名
    filename = filename ? filename + '.xls' : 'excel_data.xls';
    
    // 创建下载链接
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if (navigator.msSaveOrOpenBlob) {
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        // 创建一个链接到模拟文件下载
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}
</script>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 