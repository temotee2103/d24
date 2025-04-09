<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                给 <?php echo h($depositUser['username']); ?> 充值
            <?php else: ?>
                给 <?php echo h($depositUser['username']); ?> 转账
            <?php endif; ?>
        </h1>
        <a href="<?php echo url('user/subagents'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> 返回代理列表
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">代理信息</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th width="30%">用户名</th>
                            <td><?php echo h($depositUser['username']); ?></td>
                        </tr>
                        <tr>
                            <th>昵称</th>
                            <td><?php echo h($depositUser['nickname']); ?></td>
                        </tr>
                        <tr>
                            <th>当前余额</th>
                            <td><?php echo number_format($depositUser['balance'], 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                        <h5 class="card-title">充值表单</h5>
                    <?php else: ?>
                        <h5 class="card-title">转账表单</h5>
                        <div class="text-muted">您当前的余额: <strong><?php echo number_format($_SESSION['user']['balance'], 2); ?></strong></div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (isset($error) && !empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($success) && !empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo url('user/deposit/' . $depositUser['id']); ?>">
                        <div class="mb-3">
                            <label for="amount" class="form-label">金额</label>
                            <div class="input-group">
                                <span class="input-group-text">MYR</span>
                                <input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                                <button type="submit" class="btn btn-primary">充值</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">转账</button>
                            <?php endif; ?>
                            <a href="<?php echo url('user/view/' . $depositUser['id']); ?>" class="btn btn-secondary">返回</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 