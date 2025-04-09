<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="welcome-banner modern-card" style="background: var(--primary-gradient); color: white; margin-bottom: 2rem;">
    <div class="row align-items-center">
        <div class="col-md-2 text-center d-none d-md-block">
            <img src="<?php echo asset('images/logo.png'); ?>" alt="D24 Logo" class="img-fluid mb-3" style="max-width: 120px; filter: brightness(0) invert(1);">
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="mb-3">系统概览</h2>
                    <p class="mb-1"><i class="bi bi-calendar-check me-2"></i> 当前日期: <?php echo date('Y-m-d'); ?></p>
                    <p class="mb-1"><i class="bi bi-clock me-2"></i> 系统时间: <span id="currentTime"></span></p>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="p-2 rounded-3" style="background: rgba(255,255,255,0.1);">
                                <h6 class="mb-1">今日订单</h6>
                                <h3 class="mb-0"><?php echo isset($stats['orders_today']) ? $stats['orders_today'] : '0'; ?></h3>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-2 rounded-3" style="background: rgba(255,255,255,0.1);">
                                <h6 class="mb-1">在线用户</h6>
                                <h3 class="mb-0"><?php echo isset($stats['online_users']) ? $stats['online_users'] : '0'; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="modern-card">
            <h4 class="mb-4">账户信息</h4>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td style="width: 40%; font-weight: 500;">用户名</td>
                                <td><strong><?php echo h($user['username']); ?></strong></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 500;">用户级别</td>
                                <td><span class="badge" style="background: var(--primary-gradient);"><?php echo h($user['role']); ?></span></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 500;">状态</td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo isset($user['status']) && $user['status'] == 'active' ? '活跃' : '停用'; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 500;">佣金</td>
                                <td><?php echo isset($user['commission_rate']) ? $user['commission_rate'] . '%' : '0%'; ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 500;">代理总数</td>
                                <td><?php echo isset($user['subagent_count']) ? $user['subagent_count'] : '0'; ?> 人</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td style="width: 40%; font-weight: 500;">余额</td>
                                <td class="fw-bold" style="color: #10B981;">
                                    MYR <?php echo isset($user['balance']) ? number_format($user['balance'], 2) : '0.00'; ?>
                                    <a href="<?php echo url('user/check_balance'); ?>" class="btn btn-sm btn-outline-secondary ms-2" title="检查余额"><i class="bi bi-arrow-repeat"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 500;">未完成交易</td>
                                <td><?php echo isset($user['pending_transactions']) ? $user['pending_transactions'] : '0'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="mb-4">奖金信息 <small class="text-muted">(以MYR 1.00为单位)</small></h4>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <h5 class="card-title">一等奖</h5>
                                <p class="card-text display-6 fw-bold text-primary">MYR 3,000.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <h5 class="card-title">二等奖</h5>
                                <p class="card-text display-6 fw-bold text-success">MYR 1,000.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <h5 class="card-title">三等奖</h5>
                                <p class="card-text display-6 fw-bold text-warning">MYR 500.00</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-4">快捷操作</h3>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex flex-column align-items-center text-center">
                <div class="icon-circle mb-3" style="width: 70px; height: 70px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                    <i class="bi bi-cash"></i>
                </div>
                <h4>下注</h4>
                <p class="text-muted">创建新的下注订单</p>
                <a href="<?php echo url('order/create'); ?>" class="btn btn-gradient-primary w-100">
                    立即下注
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex flex-column align-items-center text-center">
                <div class="icon-circle mb-3" style="width: 70px; height: 70px; border-radius: 50%; background: var(--secondary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                    <i class="bi bi-list-check"></i>
                </div>
                <h4>订单查询</h4>
                <p class="text-muted">查看和管理已有订单</p>
                <a href="<?php echo url('order'); ?>" class="btn btn-gradient-secondary w-100">
                    查看订单
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex flex-column align-items-center text-center">
                <div class="icon-circle mb-3" style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #22D3EE 0%, #0EA5E9 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h4>创建下线</h4>
                <p class="text-muted">添加新的代理用户</p>
                <a href="<?php echo url('user/create'); ?>" class="btn btn-gradient-primary w-100">
                    创建用户
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex flex-column align-items-center text-center">
                <div class="icon-circle mb-3" style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #A78BFA 0%, #8B5CF6 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                    <i class="bi bi-people"></i>
                </div>
                <h4>代理管理</h4>
                <p class="text-muted">查看和管理下线用户</p>
                <a href="<?php echo url('user/subagents'); ?>" class="btn btn-gradient-secondary w-100">
                    管理下线
                </a>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-4">报表访问</h3>

<div class="row">
    <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super_admin'): ?>
    <div class="col-md-3 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box me-3" style="width: 50px; height: 50px; border-radius: 12px; background: rgba(99, 102, 241, 0.1); display: flex; align-items: center; justify-content: center; color: #6366F1; font-size: 1.5rem;">
                    <i class="bi bi-bar-chart"></i>
                </div>
                <div>
                    <h5 class="mb-1">销售报表</h5>
                    <p class="text-muted mb-0">查看销售数据分析</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?php echo url('report/sales'); ?>" class="btn btn-sm w-100" style="background: rgba(99, 102, 241, 0.1); color: #6366F1; border: none; border-radius: var(--button-border-radius);">查看报表</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box me-3" style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10B981; font-size: 1.5rem;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div>
                    <h5 class="mb-1">用户报表</h5>
                    <p class="text-muted mb-0">用户数据分析</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?php echo url('report/user'); ?>" class="btn btn-sm w-100" style="background: rgba(16, 185, 129, 0.1); color: #10B981; border: none; border-radius: var(--button-border-radius);">查看报表</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="col-md-3 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box me-3" style="width: 50px; height: 50px; border-radius: 12px; background: rgba(244, 63, 94, 0.1); display: flex; align-items: center; justify-content: center; color: #F43F5E; font-size: 1.5rem;">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <h5 class="mb-1">佣金报表</h5>
                    <p class="text-muted mb-0">佣金收入统计</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?php echo url('report/commission'); ?>" class="btn btn-sm w-100" style="background: rgba(244, 63, 94, 0.1); color: #F43F5E; border: none; border-radius: var(--button-border-radius);">查看报表</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box me-3" style="width: 50px; height: 50px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #3B82F6; font-size: 1.5rem;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h5 class="mb-1">交易记录</h5>
                    <p class="text-muted mb-0">查看交易历史</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?php echo url('report/transactions'); ?>" class="btn btn-sm w-100" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6; border: none; border-radius: var(--button-border-radius);">查看记录</a>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 

<script>
function updateCurrentTime() {
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();
    
    // 添加前导零
    hours = hours < 10 ? '0' + hours : hours;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    
    const timeString = hours + ':' + minutes + ':' + seconds;
    document.getElementById('currentTime').textContent = timeString;
}

// 页面加载时更新时间
updateCurrentTime();
// 每秒更新时间
setInterval(updateCurrentTime, 1000);
</script> 