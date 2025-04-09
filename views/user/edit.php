<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">编辑代理信息</h1>
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
                    <form method="post" action="<?php echo url('user/edit/' . $editUser['id']); ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">用户名</label>
                                <input type="text" class="form-control" id="username" value="<?php echo h($editUser['username']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">密码 (留空表示不修改)</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nickname" class="form-label">昵称</label>
                                <input type="text" class="form-control" id="nickname" name="nickname" value="<?php echo h($editUser['nickname']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">手机号码</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo h($editUser['phone']); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="commission_rate" class="form-label">佣金比例 (%)</label>
                                <input type="number" class="form-control" id="commission_rate" name="commission_rate" min="0" max="<?php echo $_SESSION['user']['commission_rate']; ?>" value="<?php echo $editUser['commission_rate']; ?>">
                                <div class="form-text">最大不能超过您的佣金比例 (<?php echo $_SESSION['user']['commission_rate']; ?>%)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">状态</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo $editUser['status'] === 'active' ? 'selected' : ''; ?>>激活</option>
                                    <option value="suspended" <?php echo $editUser['status'] === 'suspended' ? 'selected' : ''; ?>>停用</option>
                                </select>
                            </div>
                        </div>
                        
                        <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">用户角色</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="agent" <?php echo $editUser['role'] === 'agent' ? 'selected' : ''; ?>>代理</option>
                                    <option value="admin" <?php echo $editUser['role'] === 'admin' ? 'selected' : ''; ?>>管理员</option>
                                    <?php if ($editUser['role'] === 'super_admin'): ?>
                                    <option value="super_admin" selected>超级管理员</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="parent_id" class="form-label">上线用户</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">-- 无上线用户 --</option>
                                    <?php foreach ($allUsers as $potentialParent): ?>
                                        <?php if ($potentialParent['id'] != $editUser['id']): ?>
                                        <option value="<?php echo $potentialParent['id']; ?>" <?php echo $editUser['parent_id'] == $potentialParent['id'] ? 'selected' : ''; ?>>
                                            <?php echo h($potentialParent['username']); ?> (<?php echo $potentialParent['role']; ?>)
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="can_create_subagent" name="can_create_subagent" value="1" <?php echo $editUser['can_create_subagent'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="can_create_subagent">
                                    允许该代理创建下线
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">备注</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo h($editUser['notes']); ?></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">保存修改</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 