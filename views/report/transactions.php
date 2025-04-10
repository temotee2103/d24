<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>
<?php include_once ROOT_PATH . '/includes/transaction_helpers.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">交易记录</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> 打印
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="downloadCSV">
                <i class="bi bi-file-earmark-excel"></i> 导出CSV
            </button>
        </div>
    </div>
    
    <!-- 筛选条件 -->
    <div class="modern-card mb-4">
        <h5 class="mb-3">筛选条件</h5>
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
                <button type="submit" class="btn btn-gradient-primary">查询</button>
                <button type="button" class="btn btn-secondary" id="resetFilters">重置筛选</button>
            </div>
        </form>
    </div>
    
    <!-- 交易表格 -->
    <div class="modern-card">
        <div class="table-header">
            <div>
                <h5 class="mb-0">交易记录</h5>
                <p class="text-muted small mb-0 mt-1">
                    <?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days')); ?> 至 
                    <?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>
                    <?php if (!empty($transactions)): ?>
                    <span class="ms-2">共 <?php echo count($transactions); ?> 条记录</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <div class="table-responsive">
            <?php if (empty($transactions)): ?>
                <div class="table-empty-state">
                    <i class="bi bi-info-circle"></i>
                    没有找到符合条件的交易记录。
                </div>
            <?php else: ?>
                <table class="table table-hover modern-table" id="transactionsTable">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>用户</th>
                            <th>订单号</th>
                            <th class="text-center">类型</th>
                            <th class="text-end">金额</th>
                            <th class="text-end">账户余额变化</th>
                            <th>备注</th>
                            <th class="text-center">交易时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td class="text-center"><?php echo $transaction['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; background-color: #f8f9fa;">
                                            <i class="bi bi-person text-muted"></i>
                                        </div>
                                        <?php 
                                            if (isset($transaction['username'])) {
                                                echo h($transaction['username']);
                                            } else {
                                                echo '用户 ID: ' . $transaction['user_id'];
                                            }
                                        ?>
                                    </div>
                                </td>
                                <td><?php echo $transaction['order_id'] ? $transaction['order_id'] : '-'; ?></td>
                                <td class="text-center"><?php echo getTransactionTypeName($transaction['type']); ?></td>
                                <td class="text-end fw-medium"><?php echo getAmountWithSign($transaction['amount'], $transaction['type']); ?></td>
                                <td class="text-end">
                                    <?php echo number_format($transaction['balance_before'], 2); ?> → 
                                    <?php echo number_format($transaction['balance_after'], 2); ?>
                                </td>
                                <td><?php echo h($transaction['notes']); ?></td>
                                <td class="text-center small"><?php echo date('Y-m-d H:i:s', strtotime($transaction['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 筛选面板切换
    document.getElementById('toggleFilters')?.addEventListener('click', function() {
        const filterPanel = document.getElementById('filterPanel');
        if (filterPanel) {
            filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
        }
    });
    
    // 重置筛选
    document.getElementById('resetFilters').addEventListener('click', function() {
        window.location.href = '<?php echo url('report/transactions'); ?>';
    });
    
    // 导出CSV
    document.getElementById('downloadCSV').addEventListener('click', function() {
        exportTableToCSV('transactions.csv');
    });
    
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

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 