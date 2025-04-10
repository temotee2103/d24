<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="welcome-banner modern-card" style="background: var(--primary-gradient); color: white; margin-bottom: 2rem;">
    <div class="row align-items-center">
        <div class="col-md-2 text-center d-none d-md-block">
            <img src="<?php echo asset('images/logo.png'); ?>" alt="D24 Logo" class="img-fluid mb-3" style="max-width: 120px; filter: brightness(0) invert(1);">
        </div>
        <div class="col-md-10">
            <h2 class="mb-4">系统概览</h2>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="date-display p-3 rounded-3" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                        <h5 class="mb-2"><i class="bi bi-calendar-check me-2"></i> 当前日期</h5>
                        <h2 style="font-size: 2.2rem; font-weight: 600;"><?php echo date('Y-m-d'); ?></h2>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="clock-display p-3 rounded-3" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                        <h5 class="mb-2"><i class="bi bi-clock me-2"></i> 系统时间</h5>
                        <div class="digital-clock">
                            <h2 id="digitalClock" style="font-size: 2.2rem; font-weight: 600; font-family: 'Courier New', monospace;">00:00:00</h2>
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
                    <table class="table table-borderless modern-table">
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
                    <table class="table table-borderless modern-table">
                        <tbody>
                            <tr>
                                <td style="width: 40%; font-weight: 500;">余额</td>
                                <td class="fw-bold text-success">
                                    RM <?php echo isset($user['balance']) ? number_format($user['balance'], 2) : '0.00'; ?>
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
                <h4 class="mb-0 pb-3">奖金信息 <small class="text-muted">(以RM 1.00为单位)</small></h4>
                
                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr class="text-center">
                                <th>奖项</th>
                                <th>大 (RM)</th>
                                <th>小 (RM)</th>
                                <th>头二三奖A (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">头奖</span></td>
                                <td class="text-center fw-bold">2,500.00</td>
                                <td class="text-center fw-bold">3,500.00</td>
                                <td class="text-center fw-bold">4A: 6,000.00</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">二奖</span></td>
                                <td class="text-center fw-bold">1,000.00</td>
                                <td class="text-center fw-bold">2,000.00</td>
                                <td class="text-center fw-bold">4B: 6,000.00</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">三奖</span></td>
                                <td class="text-center fw-bold">500.00</td>
                                <td class="text-center fw-bold">1,000.00</td>
                                <td class="text-center fw-bold">4C: 6,000.00</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info text-white">特别奖</span></td>
                                <td class="text-center fw-bold">200.00</td>
                                <td class="text-center fw-bold">-</td>
                                <td class="text-center fw-bold">-</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-secondary">安慰奖</span></td>
                                <td class="text-center fw-bold">60.00</td>
                                <td class="text-center fw-bold">-</td>
                                <td class="text-center fw-bold">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 small text-muted">
                    <p class="mb-1">* 所有开彩种类统一使用以上奖金制度</p>
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
    <div class="col-md-4 mb-4">
        <div class="modern-card h-100">
            <div class="d-flex align-items-center">
                <div class="icon-box me-3" style="width: 50px; height: 50px; border-radius: 12px; background: rgba(236, 72, 153, 0.1); display: flex; align-items: center; justify-content: center; color: #EC4899; font-size: 1.5rem;">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div>
                    <h5 class="mb-1">财务报表</h5>
                    <p class="text-muted mb-0">综合财务数据统计</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?php echo url('report/financial'); ?>" class="btn btn-sm w-100" style="background: rgba(236, 72, 153, 0.1); color: #EC4899; border: none; border-radius: var(--button-border-radius);">查看报表</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
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
    
    <div class="col-md-4 mb-4">
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
    <?php endif; ?>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 

<script>
function updateDigitalClock() {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');
    
    const timeString = `${hours}:${minutes}:${seconds}`;
    document.getElementById('digitalClock').innerText = timeString;
    
    // Blink the separators
    const hasColons = document.getElementById('digitalClock').classList.contains('blink');
    document.getElementById('digitalClock').style.textShadow = hasColons ? 
        '0 0 10px rgba(255, 255, 255, 0.8)' : 
        '0 0 20px rgba(255, 255, 255, 0.4)';
    document.getElementById('digitalClock').classList.toggle('blink');
}

// Update clock immediately and then every second
updateDigitalClock();
setInterval(updateDigitalClock, 500); // Update every 500ms for a better blinking effect

// 切换彩票类型的脚本
document.addEventListener('DOMContentLoaded', function() {
    const lotterBtns = document.querySelectorAll('.lottery-type-btn');
    lotterBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-target');
            // 隐藏所有奖金信息
            document.querySelectorAll('.lottery-prize-info').forEach(el => {
                el.classList.add('d-none');
            });
            // 显示目标奖金信息
            document.getElementById(target).classList.remove('d-none');
            // 更新按钮激活状态
            lotterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            // 更新下拉菜单标题
            document.getElementById('lotteryTypeDropdown').textContent = this.textContent;
        });
    });
});
</script> 