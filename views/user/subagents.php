<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">代理管理</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if (isset($_SESSION['user']['can_create_subagent']) && $_SESSION['user']['can_create_subagent']): ?>
                <a href="<?php echo url('user/create'); ?>" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> 创建下线
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($subagents)): ?>
                <div class="alert alert-info">
                    您目前没有下线代理。
                    <?php if (isset($_SESSION['user']['can_create_subagent']) && $_SESSION['user']['can_create_subagent']): ?>
                        <a href="<?php echo url('user/create'); ?>">点击此处创建下线</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">用户名</th>
                                        <th scope="col">昵称</th>
                                        <th scope="col">佣金比例</th>
                                        <th scope="col">可开下线</th>
                                        <th scope="col">余额</th>
                                        <th scope="col">状态</th>
                                        <th scope="col">创建时间</th>
                                        <th scope="col">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subagents as $agent): ?>
                                        <tr>
                                            <td><?php echo $agent['id']; ?></td>
                                            <td><?php echo h($agent['username']); ?></td>
                                            <td><?php echo h($agent['nickname']); ?></td>
                                            <td><?php echo $agent['commission_rate']; ?>%</td>
                                            <td>
                                                <?php if ($agent['can_create_subagent']): ?>
                                                    <span class="badge bg-success">可以</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">不可以</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($agent['balance'], 2); ?></td>
                                            <td>
                                                <?php if ($agent['status'] === 'active'): ?>
                                                    <span class="badge bg-success">激活</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">停用</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($agent['created_at'])); ?></td>
                                            <td class="text-nowrap">
                                                <a href="<?php echo url('user/view/' . $agent['id']); ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> 查看
                                                </a>
                                                <a href="<?php echo url('user/edit/' . $agent['id']); ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i> 编辑
                                                </a>
                                                <a href="<?php echo url('user/deposit/' . $agent['id']); ?>" class="btn btn-sm btn-success">
                                                    <i class="bi bi-cash"></i> <?php echo $_SESSION['user']['role'] === 'super_admin' ? '充值' : '转账'; ?>
                                                </a>
                                                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                                                <a href="<?php echo url('user/delete/' . $agent['id']); ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('确定要删除用户 <?php echo h($agent['username']); ?> 吗？此操作不可恢复！');">
                                                    <i class="bi bi-trash"></i> 删除
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 