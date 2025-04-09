<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">交易记录</h1>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">筛选条件</h5>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleFilters">
                    显示/隐藏筛选
                </button>
            </div>
        </div>
        <div class="card-body" id="filterPanel" style="display: none;">
            <form method="get" action="<?php echo url('report/transactions'); ?>" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">开始日期</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days')); ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">结束日期</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">交易类型</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">全部类型</option>
                        <option value="deposit" <?php echo (isset($_GET['type']) && $_GET['type'] === 'deposit') ? 'selected' : ''; ?>>充值</option>
                        <option value="withdraw" <?php echo (isset($_GET['type']) && $_GET['type'] === 'withdraw') ? 'selected' : ''; ?>>下注</option>
                        <option value="commission" <?php echo (isset($_GET['type']) && $_GET['type'] === 'commission') ? 'selected' : ''; ?>>佣金</option>
                        <option value="refund" <?php echo (isset($_GET['type']) && $_GET['type'] === 'refund') ? 'selected' : ''; ?>>退款</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="min_amount" class="form-label">最小金额</label>
                    <input type="number" class="form-control" id="min_amount" name="min_amount" value="<?php echo isset($_GET['min_amount']) ? $_GET['min_amount'] : ''; ?>" step="0.01" min="0">
                </div>
                <div class="col-md-12 mt-3">
                    <button type="submit" class="btn btn-primary">查询</button>
                    <button type="button" class="btn btn-secondary" id="resetFilters">重置筛选</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">交易记录</h5>
            <div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="downloadCSV">
                        <i class="fas fa-download"></i> 导出CSV
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info">
                    没有找到符合条件的交易记录。
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>用户</th>
                                <th>订单号</th>
                                <th>类型</th>
                                <th>金额</th>
                                <th>账户余额变化</th>
                                <th>备注</th>
                                <th>交易时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr class="<?php echo getTransactionClass($transaction['type']); ?>">
                                    <td><?php echo $transaction['id']; ?></td>
                                    <td>
                                        <?php 
                                            if (isset($transaction['username'])) {
                                                echo h($transaction['username']);
                                            } else {
                                                echo '用户 ID: ' . $transaction['user_id'];
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo $transaction['order_id'] ? $transaction['order_id'] : '-'; ?></td>
                                    <td><?php echo getTransactionTypeName($transaction['type']); ?></td>
                                    <td><?php echo getAmountWithSign($transaction['amount'], $transaction['type']); ?></td>
                                    <td>
                                        <?php echo number_format($transaction['balance_before'], 2); ?> → 
                                        <?php echo number_format($transaction['balance_after'], 2); ?>
                                    </td>
                                    <td><?php echo h($transaction['notes']); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($transaction['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">交易统计</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <th>总交易笔数:</th>
                                        <td><?php echo count($transactions); ?></td>
                                    </tr>
                                    <tr>
                                        <th>充值总额:</th>
                                        <td><?php echo calculateTotalByType($transactions, 'deposit'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>下注总额:</th>
                                        <td><?php echo calculateTotalByType($transactions, 'withdraw'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>佣金总额:</th>
                                        <td><?php echo calculateTotalByType($transactions, 'commission'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <canvas id="transactionTypeChart" width="400" height="300"></canvas>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 筛选面板切换
    document.getElementById('toggleFilters').addEventListener('click', function() {
        const filterPanel = document.getElementById('filterPanel');
        filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
    });
    
    // 重置筛选
    document.getElementById('resetFilters').addEventListener('click', function() {
        window.location.href = '<?php echo url('report/transactions'); ?>';
    });
    
    // 导出CSV
    document.getElementById('downloadCSV').addEventListener('click', function() {
        exportTableToCSV('transactions.csv');
    });
    
    <?php if (!empty($transactions)): ?>
    // 交易类型图表
    const ctxType = document.getElementById('transactionTypeChart').getContext('2d');
    const transactionTypes = {
        'deposit': 0,
        'withdraw': 0,
        'commission': 0,
        'refund': 0
    };
    
    <?php foreach ($transactions as $transaction): ?>
        transactionTypes['<?php echo $transaction['type']; ?>'] += 1;
    <?php endforeach; ?>
    
    const typeLabels = {
        'deposit': '充值',
        'withdraw': '下注',
        'commission': '佣金',
        'refund': '退款'
    };
    
    const typeColors = {
        'deposit': 'rgba(75, 192, 192, 0.7)',
        'withdraw': 'rgba(255, 99, 132, 0.7)',
        'commission': 'rgba(54, 162, 235, 0.7)',
        'refund': 'rgba(255, 206, 86, 0.7)'
    };
    
    const labels = [];
    const data = [];
    const backgroundColor = [];
    
    for (const type in transactionTypes) {
        if (transactionTypes[type] > 0) {
            labels.push(typeLabels[type]);
            data.push(transactionTypes[type]);
            backgroundColor.push(typeColors[type]);
        }
    }
    
    new Chart(ctxType, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColor,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                title: {
                    display: true,
                    text: '交易类型分布'
                }
            }
        }
    });
    <?php endif; ?>
    
    // 导出表格为CSV
    function exportTableToCSV(filename) {
        const csv = [];
        const rows = document.querySelectorAll('#transactionsTable tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
            }
            
            csv.push(row.join(','));
        }
        
        // 下载CSV文件
        const csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
        const downloadLink = document.createElement('a');
        
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }
});
</script>

<?php
// 辅助函数
function getTransactionClass($type) {
    switch ($type) {
        case 'deposit':
            return 'table-success';
        case 'withdraw':
            return 'table-danger';
        case 'commission':
            return 'table-primary';
        case 'refund':
            return 'table-warning';
        default:
            return '';
    }
}

function getTransactionTypeName($type) {
    switch ($type) {
        case 'deposit':
            return '<span class="badge bg-success">充值</span>';
        case 'withdraw':
            return '<span class="badge bg-danger">下注</span>';
        case 'commission':
            return '<span class="badge bg-primary">佣金</span>';
        case 'refund':
            return '<span class="badge bg-warning text-dark">退款</span>';
        default:
            return '<span class="badge bg-secondary">其他</span>';
    }
}

function getAmountWithSign($amount, $type) {
    $formatted = number_format($amount, 2);
    switch ($type) {
        case 'deposit':
        case 'commission':
        case 'refund':
            return '<span class="text-success">+' . $formatted . '</span>';
        case 'withdraw':
            return '<span class="text-danger">-' . $formatted . '</span>';
        default:
            return $formatted;
    }
}

function calculateTotalByType($transactions, $type) {
    $total = 0;
    foreach ($transactions as $transaction) {
        if ($transaction['type'] === $type) {
            $total += $transaction['amount'];
        }
    }
    return number_format($total, 2);
}
?>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 