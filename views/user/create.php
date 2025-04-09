<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $_SESSION['user']['role'] === 'super_admin' ? '创建用户' : '创建下线代理'; ?></h1>
        <a href="<?php echo url('user/subagents'); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> 返回代理列表
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?php echo url('user/create'); ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">用户名 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">密码 <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nickname" class="form-label">昵称</label>
                                <input type="text" class="form-control" id="nickname" name="nickname">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">手机号码</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="commission_rate" class="form-label">佣金比例 (%)</label>
                                <input type="number" class="form-control" id="commission_rate" name="commission_rate" min="0" max="<?php echo isset($_SESSION['user']['commission_rate']) ? $_SESSION['user']['commission_rate'] : 0; ?>" value="0">
                                <div class="form-text">最大不能超过您的佣金比例 (<?php echo isset($_SESSION['user']['commission_rate']) ? $_SESSION['user']['commission_rate'] : 0; ?>%)</div>
                            </div>
                            <div class="col-md-6">
                                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                                <label for="role" class="form-label">用户角色</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="agent">代理</option>
                                    <option value="admin">管理员</option>
                                </select>
                                <?php else: ?>
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="can_create_subagent" name="can_create_subagent" value="1">
                                    <label class="form-check-label" for="can_create_subagent">
                                        允许该代理创建下线
                                    </label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="can_create_subagent" name="can_create_subagent" value="1">
                                <label class="form-check-label" for="can_create_subagent">
                                    允许该用户创建下线
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">备注</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">创建代理</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 