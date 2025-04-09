<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">账户余额检查</h1>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">余额信息对比</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                此页面用于检查余额信息是否同步，可以确认您的真实余额。
            </div>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>数据来源</th>
                        <th>余额值</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>session中的余额</td>
                        <td><?php echo isset($_SESSION['user']['balance']) ? number_format($_SESSION['user']['balance'], 2) : '未设置'; ?></td>
                    </tr>
                    <tr>
                        <td>数据库中的余额</td>
                        <td><?php echo isset($dbUser['balance']) ? number_format($dbUser['balance'], 2) : '未获取'; ?></td>
                    </tr>
                    <tr>
                        <td>最近的交易记录后余额</td>
                        <td><?php echo isset($lastTransaction['balance_after']) ? number_format($lastTransaction['balance_after'], 2) : '无交易记录'; ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mt-4">
                <a href="<?php echo url('user/sync_balance'); ?>" class="btn btn-primary">同步余额</a>
                <a href="<?php echo url('home'); ?>" class="btn btn-secondary">返回主页</a>
            </div>
        </div>
    </div>
    
    <?php if (isset($transactions) && !empty($transactions)): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">最近5笔交易记录</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>类型</th>
                            <th>金额</th>
                            <th>交易前余额</th>
                            <th>交易后余额</th>
                            <th>时间</th>
                            <th>备注</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo $transaction['id']; ?></td>
                            <td><?php echo $transaction['type']; ?></td>
                            <td><?php echo number_format($transaction['amount'], 2); ?></td>
                            <td><?php echo number_format($transaction['balance_before'], 2); ?></td>
                            <td><?php echo number_format($transaction['balance_after'], 2); ?></td>
                            <td><?php echo $transaction['created_at']; ?></td>
                            <td><?php echo h($transaction['notes']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 