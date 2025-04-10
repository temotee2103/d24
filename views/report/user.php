<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">用户报表</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> 打印
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportTableToExcel('userTable', 'user_report')">
                <i class="bi bi-file-earmark-excel"></i> 导出
            </button>
        </div>
    </div>
    
    <!-- 筛选条件 -->
    <div class="modern-card mb-4">
        <h5 class="mb-3">筛选条件</h5>
        <form method="get" action="<?php echo url('report/user'); ?>" class="row g-3">
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
    
    <!-- 用户表格 - 优化设计 -->
    <div class="modern-card">
        <div class="table-header">
            <div>
                <h5 class="mb-0">用户数据</h5>
                <p class="text-muted small mb-0 mt-1">共 <?php echo count($displayUsers); ?> 条记录</p>
            </div>
        </div>
        
        <div class="table-responsive">
            <?php if (!empty($displayUsers)): ?>
                <table class="table table-hover modern-table" id="userTable">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>用户名</th>
                            <th class="text-center">角色</th>
                            <th class="text-end">充值总额</th>
                            <th class="text-end">佣金余额</th>
                            <th class="text-end">消费总额</th>
                            <th class="text-center">登录次数</th>
                            <th class="text-center">注册时间</th>
                            <th class="text-center">最后登录</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($displayUsers as $user): ?>
                            <tr>
                                <td class="text-center"><?= $user['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; background-color: #f8f9fa;">
                                            <i class="bi bi-person text-muted"></i>
                                        </div>
                                        <?= htmlspecialchars($user['username']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($user['role'] == 'admin'): ?>
                                        <span class="badge rounded-pill bg-danger">管理员</span>
                                    <?php elseif ($user['role'] == 'agent'): ?>
                                        <span class="badge rounded-pill bg-primary">代理</span>
                                    <?php elseif ($user['role'] == 'user'): ?>
                                        <span class="badge rounded-pill bg-success">普通用户</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-info"><?= htmlspecialchars($user['role']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-medium"><?= 'RM'.number_format($user['total_deposits'], 2) ?></td>
                                <td class="text-end fw-medium"><?= 'RM'.number_format($user['commission_balance'], 2) ?></td>
                                <td class="text-end fw-medium"><?= 'RM'.number_format($user['total_spent'], 2) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark"><?= $user['login_count'] ?></span>
                                </td>
                                <td class="text-center small"><?= $user['created_at'] ?></td>
                                <td class="text-center small">
                                    <?php if (empty($user['last_login'])): ?>
                                        <span class="text-muted">从未登录</span>
                                    <?php else: ?>
                                        <?= $user['last_login'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">合计:</td>
                            <td class="text-end">
                                <?php 
                                    $totalDeposits = 0;
                                    foreach ($displayUsers as $user) {
                                        $totalDeposits += $user['total_deposits'];
                                    }
                                    echo 'RM'.number_format($totalDeposits, 2);
                                ?>
                            </td>
                            <td class="text-end">
                                <?php 
                                    $totalCommission = 0;
                                    foreach ($displayUsers as $user) {
                                        $totalCommission += $user['commission_balance'];
                                    }
                                    echo 'RM'.number_format($totalCommission, 2);
                                ?>
                            </td>
                            <td class="text-end">
                                <?php 
                                    $totalSpent = 0;
                                    foreach ($displayUsers as $user) {
                                        $totalSpent += $user['total_spent'];
                                    }
                                    echo 'RM'.number_format($totalSpent, 2);
                                ?>
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <div class="table-empty-state">
                    <i class="bi bi-info-circle"></i>
                    没有找到用户数据。请调整筛选条件重试。
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
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