<?php 
include_once ROOT_PATH . '/views/layout/header.php'; 
include_once ROOT_PATH . '/models/UserModel.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $_SESSION['user']['role'] === 'super_admin' ? '用户管理' : '代理管理'; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if (isset($_SESSION['user']['can_create_subagent']) && $_SESSION['user']['can_create_subagent']): ?>
                <a href="<?php echo url('user/create'); ?>" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> <?php echo $_SESSION['user']['role'] === 'super_admin' ? '创建用户' : '创建下线'; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($subagents)): ?>
                <div class="alert alert-info">
                    <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                        系统中暂无用户。
                    <?php else: ?>
                        您目前没有下线代理。
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user']['can_create_subagent']) && $_SESSION['user']['can_create_subagent']): ?>
                        <a href="<?php echo url('user/create'); ?>"><?php echo $_SESSION['user']['role'] === 'super_admin' ? '点击此处创建用户' : '点击此处创建下线'; ?></a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <!-- 批量操作表单 -->
                        <form id="batchActionForm" method="post" action="<?php echo url('user/batch_action'); ?>">
                            <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-sm btn-secondary rounded-3 me-2" id="selectAll">全选</button>
                                        <button type="button" class="btn btn-sm btn-secondary rounded-3 me-3" id="deselectAll">取消选择</button>
                                        <input type="hidden" name="batch_action" id="batchActionInput" value="">
                                        <button type="submit" id="batchDeactivate" class="btn btn-sm btn-warning rounded-3 me-2 d-none" data-action="deactivate">
                                            <i class="bi bi-toggle-off"></i> 批量停用
                                        </button>
                                        <button type="submit" id="batchDelete" class="btn btn-sm btn-danger rounded-3 d-none" data-action="delete">
                                            <i class="bi bi-trash"></i> 批量删除
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="btn-group shadow-sm">
                                        <a href="<?php echo url('user/subagents?sort=id&order=asc'); ?>" class="btn btn-sm btn-secondary <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'id' && (!isset($_GET['order']) || $_GET['order'] == 'asc')) ? 'active' : ''; ?>">
                                            ID ↑
                                        </a>
                                        <a href="<?php echo url('user/subagents?sort=id&order=desc'); ?>" class="btn btn-sm btn-secondary <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'id' && isset($_GET['order']) && $_GET['order'] == 'desc') ? 'active' : ''; ?>">
                                            ID ↓
                                        </a>
                                        <a href="<?php echo url('user/subagents?sort=balance&order=desc'); ?>" class="btn btn-sm btn-secondary <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'balance' && isset($_GET['order']) && $_GET['order'] == 'desc') ? 'active' : ''; ?>">
                                            余额 ↓
                                        </a>
                                        <a href="<?php echo url('user/subagents?sort=created_at&order=desc'); ?>" class="btn btn-sm btn-secondary <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'created_at' && isset($_GET['order']) && $_GET['order'] == 'desc') ? 'active' : ''; ?>">
                                            最新
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="table-responsive">
                                <table class="table table-hover modern-table user-management-table" style="border-collapse: separate !important; border-spacing: 0 !important;">
                                    <thead>
                                        <tr>
                                            <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                                            <th scope="col" width="40">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                            </th>
                                            <?php endif; ?>
                                            <th scope="col">ID</th>
                                            <th scope="col">用户名</th>
                                            <th scope="col">昵称</th>
                                            <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                                            <th scope="col">角色</th>
                                            <th scope="col">上级</th>
                                            <?php endif; ?>
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
                                            <tr class="user-row">
                                                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                                                <td>
                                                    <input type="checkbox" class="form-check-input user-checkbox" name="selected_users[]" value="<?php echo $agent['id']; ?>">
                                                </td>
                                                <?php endif; ?>
                                                <td><?php echo $agent['id']; ?></td>
                                                <td><?php echo h($agent['username']); ?></td>
                                                <td><?php echo h($agent['nickname']); ?></td>
                                                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                                                <td>
                                                    <?php 
                                                        $roleLabels = [
                                                            'user' => '<span class="badge bg-info user-badge">用户</span>',
                                                            'agent' => '<span class="badge bg-primary user-badge">代理</span>',
                                                            'admin' => '<span class="badge bg-warning user-badge">管理员</span>',
                                                            'super_admin' => '<span class="badge bg-danger user-badge">超级管理员</span>'
                                                        ];
                                                        echo $roleLabels[$agent['role']] ?? $agent['role'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        if (!empty($agent['parent_id'])) {
                                                            $parent = (new UserModel())->getUserById($agent['parent_id']);
                                                            echo $parent ? h($parent['username']) : '未知';
                                                        } else {
                                                            echo '<span class="text-muted">无</span>';
                                                        }
                                                    ?>
                                                </td>
                                                <?php endif; ?>
                                                <td><?php echo $agent['commission_rate']; ?>%</td>
                                                <td>
                                                    <?php if ($agent['can_create_subagent']): ?>
                                                        <span class="badge bg-success status-badge">可以</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary status-badge">不可以</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo number_format($agent['balance'], 2); ?></td>
                                                <td>
                                                    <?php if ($agent['status'] === 'active'): ?>
                                                        <span class="badge bg-success status-badge">激活</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger status-badge">停用</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($agent['created_at'])); ?></td>
                                                <td class="text-nowrap actions-column">
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
                                                        <?php if ($agent['status'] === 'active'): ?>
                                                        <a href="<?php echo url('user/toggle_status/' . $agent['id']); ?>" 
                                                           class="btn btn-sm btn-secondary"
                                                           onclick="return confirm('确定要停用用户 <?php echo h($agent['username']); ?> 吗？');">
                                                            <i class="bi bi-toggle-off"></i> 停用
                                                        </a>
                                                        <?php else: ?>
                                                        <a href="<?php echo url('user/toggle_status/' . $agent['id']); ?>" 
                                                           class="btn btn-sm btn-success"
                                                           onclick="return confirm('确定要激活用户 <?php echo h($agent['username']); ?> 吗？');">
                                                            <i class="bi bi-toggle-on"></i> 激活
                                                        </a>
                                                        <?php endif; ?>
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
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 批量操作的JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 全选/取消选择功能
    const checkAll = document.getElementById('checkAll');
    const selectAll = document.getElementById('selectAll');
    const deselectAll = document.getElementById('deselectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const batchDeactivate = document.getElementById('batchDeactivate');
    const batchDelete = document.getElementById('batchDelete');
    const batchActionInput = document.getElementById('batchActionInput');
    
    // 更新批量操作按钮显示状态
    function updateBatchButtons() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        if (checkedCount > 0) {
            batchDeactivate.classList.remove('d-none');
            batchDelete.classList.remove('d-none');
        } else {
            batchDeactivate.classList.add('d-none');
            batchDelete.classList.add('d-none');
        }
    }
    
    // 添加批量操作按钮点击事件
    if (batchDeactivate) {
        batchDeactivate.addEventListener('click', function(e) {
            batchActionInput.value = 'deactivate';
        });
    }
    
    if (batchDelete) {
        batchDelete.addEventListener('click', function(e) {
            if (!confirm('确定要删除所选用户吗？此操作不可恢复！')) {
                e.preventDefault();
                return false;
            }
            batchActionInput.value = 'delete';
        });
    }
    
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBatchButtons();
        });
    }
    
    if (selectAll) {
        selectAll.addEventListener('click', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            if (checkAll) checkAll.checked = true;
            updateBatchButtons();
        });
    }
    
    if (deselectAll) {
        deselectAll.addEventListener('click', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            if (checkAll) checkAll.checked = false;
            updateBatchButtons();
        });
    }
    
    // 为每个用户复选框添加change事件
    userCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateBatchButtons);
    });
    
    // 批量操作表单提交前验证
    const batchActionForm = document.getElementById('batchActionForm');
    if (batchActionForm) {
        batchActionForm.addEventListener('submit', function(e) {
            const action = batchActionInput.value;
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            
            if (action === '') {
                e.preventDefault();
                alert('请选择要执行的批量操作');
                return false;
            }
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('请至少选择一个用户');
                return false;
            }
            
            return true;
        });
    }
    
    // 初始化按钮状态
    updateBatchButtons();
});
</script>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 