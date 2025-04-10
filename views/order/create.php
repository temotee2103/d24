<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">下注</h1>
    </div>
    
    <!-- Opening Dates Section -->
    <div class="card mb-4 modern-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table text-center mb-0">
                    <thead>
                        <tr>
                            <th class="bg-primary text-white"></th>
                            <th class="bg-warning">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span>万能 (#1)</span>
                                    <img src="<?php echo url('assets/images/lottery/magnum.png'); ?>" alt="万能" width="25" class="ms-1">
                                </div>
                            </th>
                            <th class="bg-primary text-white">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span>四合彩 (#2)</span>
                                    <img src="<?php echo url('assets/images/lottery/damacai.png'); ?>" alt="四合彩" width="25" class="ms-1">
                                </div>
                            </th>
                            <th class="bg-danger text-white">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span>多多 (#3)</span>
                                    <img src="<?php echo url('assets/images/lottery/toto.png'); ?>" alt="多多" width="25" class="ms-1">
                                </div>
                            </th>
                            <th class="bg-info text-white">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span>新加坡 (#4)</span>
                                    <img src="<?php echo url('assets/images/lottery/singapore.png'); ?>" alt="新加坡" width="25" class="ms-1">
                                </div>
                            </th>
                            <th class="bg-danger text-white">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span>沙巴 (#5)</span>
                                    <img src="<?php echo url('assets/images/lottery/sabah.png'); ?>" alt="沙巴" width="25" class="ms-1">
                                </div>
                            </th>
                            <th class="bg-success text-white">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span>山打根 (#6)</span>
                                    <img src="<?php echo url('assets/images/lottery/sandakan.png'); ?>" alt="山打根" width="25" class="ms-1">
                                </div>
                            </th>
                            <th class="bg-success text-white">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span>砂拉越 (#7)</span>
                                    <img src="<?php echo url('assets/images/lottery/sarawak.png'); ?>" alt="砂拉越" width="25" class="ms-1">
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 显示本周的开奖日期
                        $today = new DateTime();
                        $startOfWeek = clone $today;
                        $startOfWeek->modify('this week monday');
                        
                        $days = ['一', '二', '三', '四', '五', '六', '日'];
                        $drawDays = [3, 6, 7]; // 周三、周六、周日是开奖日
                        
                        for ($i = 0; $i < 7; $i++) {
                            $currentDay = clone $startOfWeek;
                            $currentDay->modify("+$i days");
                            $dayNum = $currentDay->format('N'); // 1-7
                            $isDrawDay = in_array($dayNum, $drawDays);
                            $dateFormat = $currentDay->format('m-d');
                            
                            // 高亮当前日期
                            $isToday = $currentDay->format('Y-m-d') === $today->format('Y-m-d');
                            $rowClass = $isToday ? 'table-primary' : '';
                            
                            echo "<tr class=\"$rowClass\">";
                            echo "<td class=\"fw-bold bg-light\">星期{$days[$i]}</td>";
                            
                            // 对于每个彩种，如果是开奖日则显示日期，否则显示 -
                            for ($j = 0; $j < 7; $j++) {
                                if ($isDrawDay) {
                                    echo "<td>{$dateFormat}</td>";
                                } else {
                                    echo "<td>-</td>";
                                }
                            }
                            
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End Opening Dates Section -->
    
    <!-- User Info Section -->
    <?php if (isset($user) && $user): ?>
    <div class="card mb-4 modern-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-borderless modern-table mb-0" style="color: #333;">
                    <thead style="background-color: #ffb74d;">
                        <tr>
                            <th>Account</th>
                            <th>Credit</th>
                            <th>Available</th>
                            <th>Bet Format</th>
                            <th>Box / iBox</th>
                            <th>Draw Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo h($user['username']); ?></td>
                            <td><?php echo number_format($user['credit_limit'], 2); // Assuming credit_limit is total credit ?></td>
                            <td><?php echo number_format($user['balance'], 2); ?></td>
                            <td>B-S-4A-4B-4C</td> <!-- Placeholder -->
                            <td>* / **</td> <!-- Placeholder -->
                            <td>M-P-T-S-B-K-W</td> <!-- Placeholder -->
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- End User Info Section -->
    
    <div class="row">
        <div class="col-md-12">
            <?php /* Remove old flash message alerts
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
            */ ?>
            
            <div class="card mb-4 modern-card">
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="<?php echo url('order/create'); ?>" id="betForm">
                                <div class="mb-3">
                                    <label for="bet_content" class="form-label">输入</label>
                                    <textarea class="form-control" id="bet_content" name="bet_content" rows="15"><?php echo isset($bet_content) ? h($bet_content) : 'D\n#'; ?></textarea>
                                </div>
                                
                                <input type="hidden" name="confirm" value="1" id="confirmField">
                                
                                <div class="d-flex">
                                    <button type="submit" class="btn me-2" id="submitBet" style="background-color: #dc3545; color: white; border: none;">
                                        <i class="bi bi-calculator"></i> 投注
                                    </button>
                                    <button type="button" class="btn" id="clearButton" style="background-color: #6c757d; color: white; border: none;">
                                        <i class="bi bi-trash"></i> 清除
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="receipt" class="form-label">收据</label>
                                <textarea class="form-control" id="receipt" rows="15" readonly><?php
                                    // 恢复原始复杂的收据逻辑
                                    if (isset($parsed_result) && $parsed_result['valid']) {
                                        // 获取订单信息
                                        $order_id = isset($order) && isset($order['id']) ? $order['id'] : (date('ymd') . rand(10, 99) . '(' . rand(1, 9) . ')');
                                        $order_number = isset($order) && isset($order['order_number']) ? $order['order_number'] : ('订单' . date('YmdHis'));
                                        
                                        // 获取用户名
                                        $username = $_SESSION['user']['username'];
                                        
                                        // 期（日期）
                                        $date = date('d/m');
                                        
                                        // 获取彩种类型
                                        $lottery_types = [];
                                        foreach ($parsed_result['items'] as $item) {
                                            if (isset($item['lottery_type'])) {
                                                $lottery_types = array_merge($lottery_types, $item['lottery_type']);
                                            }
                                        }
                                        $lottery_types = array_unique($lottery_types);
                                        $lottery_str = count($lottery_types) > 0 ? '*' . implode('', $lottery_types) : '*MPTS';
                                        
                                        // 购买的号码和下注种类 (按号码分组)
                                        $grouped_bets = [];
                                        foreach ($parsed_result['items'] as $item) {
                                            if (isset($item['number'])) {
                                                $number = $item['number'];
                                                $displayBetType = $item['bet_type_code'] ?? $item['bet_type'] ?? '?';
                                                $amount = $item['original_amount'];
                                                
                                                if (!isset($grouped_bets[$number])) {
                                                    $grouped_bets[$number] = [];
                                                }
                                                $grouped_bets[$number][] = $displayBetType . $amount;
                                            }
                                        }
                                        
                                        // 生成最终的投注详情字符串
                                        $bet_details = [];
                                        foreach ($grouped_bets as $number => $details) {
                                            $bet_details[] = $number . '= ' . implode(' ', $details);
                                        }
                                        $bet_str = implode("\n", $bet_details);
                                        
                                        // 输出收据
                                        echo "{$order_number}\n";
                                        echo "{$username}\n";
                                        echo "{$date}\n";
                                        echo "{$lottery_str}\n";
                                        echo "{$bet_str}\n";
                                        echo "\n";
                                        echo "GT={$parsed_result['total']}\n";
                                    }
                                ?></textarea>
                            </div>
                            
                            <div class="d-flex">
                                <button type="button" class="btn me-2" id="copyReceipt" style="background-color: #198754; color: white; border: none;">
                                    <i class="bi bi-clipboard"></i> 复制收据
                                </button>
                                <button type="button" class="btn" id="whatsappShare" style="background-color: #0d6efd; color: white; border: none;">
                                    <i class="bi bi-whatsapp"></i> 发送到WhatsApp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-12 mt-4">
                <div class="card modern-card">
                    <div class="card-header">
                        <h5 class="card-title">下注说明</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table modern-table">
                                        <thead>
                                            <tr>
                                                <th>输入格式</th>
                                                <th>例子</th>
                                                <th>用法</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>D</td>
                                                <td>D<br>D1<br>D2<br>D3<br>D4</td>
                                                <td>D = 最近一期<br>D1 = 最近一期<br>D2 = 最近两期<br>D3 = 最近三期<br>D4 = 最近四期<br>每周三、周六、周日开奖</td>
                                            </tr>
                                            <tr>
                                                <td>#</td>
                                                <td>#1234<br>#13<br>#23<br>#1<br>#4</td>
                                                <td>彩票种类<br>#1-万能, #2-四合彩, #3-多多<br>#4-新加坡, #5-沙巴<br>#6-山打根, #7-砂拉越</td>
                                            </tr>
                                            <tr>
                                                <td>下注号码 #A #A #ABC</td>
                                                <td>1234#1#2#3#4#5</td>
                                                <td>下注 ( 1234 - B1 : S2 : A3 : ABC4 : SA5 )<br>数字B1 = 大，S = 小，SA = 4A<br>对于只下注#ABC只能入 1234###3#4<br>对于只下注4/5A只能入 1234####5</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table modern-table">
                                        <thead>
                                            <tr>
                                                <th>输入</th>
                                                <th>说明</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1234#1#2</td>
                                                <td>1234-B1-S2</td>
                                            </tr>
                                            <tr>
                                                <td>1234###1</td>
                                                <td>1234-SA1</td>
                                            </tr>
                                            <tr>
                                                <td>*1234*</td>
                                                <td>来回1234 ( 总数 = 4 )<br>4D (1234, 4321)<br>3D (234, 321)</td>
                                            </tr>
                                            <tr>
                                                <td>*1234</td>
                                                <td>全打 1234 (4D = 24x)
                                            </tr>
                                            <tr>
                                                <td>**1234</td>
                                                <td>全保 1234 (4D = 24x)
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 

<script>
// 等待页面完全加载
document.addEventListener('DOMContentLoaded', function() {
    console.log('页面加载完成');
    
    // Check for popup message from PHP
    <?php if (isset($popup_message) && $popup_message): ?>
    alert("<?php echo addslashes($popup_message['text']); ?>");
    console.log('Popup type: <?php echo $popup_message['type']; ?>');
    <?php endif; ?>
    
    // 获取表单和按钮元素
    const betForm = document.getElementById('betForm');
    const submitBetBtn = document.getElementById('submitBet');
    const clearButton = document.getElementById('clearButton');
    const copyReceipt = document.getElementById('copyReceipt');
    const whatsappShare = document.getElementById('whatsappShare');
    const betContent = document.getElementById('bet_content');
    const receipt = document.getElementById('receipt');
    const confirmField = document.getElementById('confirmField');
    
    // 记录调试信息
    console.log('表单:', betForm ? '已找到' : '未找到');
    console.log('投注按钮:', submitBetBtn ? '已找到' : '未找到');
    console.log('清除按钮:', clearButton ? '已找到' : '未找到');
    console.log('复制按钮:', copyReceipt ? '已找到' : '未找到');
    console.log('下注内容框:', betContent ? '已找到' : '未找到');
    console.log('收据框:', receipt ? '已找到' : '未找到');
    console.log('确认字段:', confirmField ? '已找到' : '未找到');
    
    // 添加投注按钮点击监听器
    if (submitBetBtn) {
        submitBetBtn.addEventListener('click', function(e) {
            console.log('===== 投注按钮被点击 =====');
            console.log('投注内容:', betContent ? betContent.value : '未找到内容');
            console.log('表单动作:', betForm ? betForm.action : '未找到表单');
            
            // 不阻止默认行为，继续正常提交
        });
    }
    
    // 处理表单提交
    if (betForm) {
        betForm.addEventListener('submit', function(e) {
            console.log('===== 表单提交被触发 =====');
            console.log('提交类型: 预览/计算');
            console.log('表单动作:', this.action);
            console.log('表单方法:', this.method);
            console.log('投注内容长度:', betContent ? betContent.value.length : '未知');
            
            // 验证输入不为空
            if (!betContent || !betContent.value.trim()) {
                console.error('验证失败: 下注内容为空');
                alert('下注内容不能为空');
                e.preventDefault();
                return false;
            }
            
            // 确保确认字段存在且值为1
            if (confirmField) {
                confirmField.value = '1';
                console.log('确认字段值设置为:', confirmField.value);
            } else {
                console.error('确认字段不存在，创建一个新的');
                const newConfirmField = document.createElement('input');
                newConfirmField.type = 'hidden';
                newConfirmField.name = 'confirm';
                newConfirmField.value = '1';
                betForm.appendChild(newConfirmField);
            }
            
            console.log('表单提交验证通过，继续提交流程');
            // 表单正常提交
            return true;
        });
    }
    
    // 清除按钮功能
    if (clearButton) {
        clearButton.addEventListener('click', function() {
            console.log('清除按钮被点击');
            if (betContent) betContent.value = 'D\n#';
            if (receipt) receipt.value = '';
        });
    }
    
    // 复制收据功能
    if (copyReceipt) {
        copyReceipt.addEventListener('click', function() {
            console.log('复制收据按钮被点击');
            let receiptText = receipt ? receipt.value : '';
            if (receiptText) {
                try {
                    // 复制到剪贴板
                    navigator.clipboard.writeText(receiptText)
                        .then(() => {
                            alert('收据已复制到剪贴板!');
                            console.log('收据复制成功');
                        })
                        .catch(err => {
                            console.error('剪贴板复制失败:', err);
                            // 回退方法
                            fallbackCopyTextToClipboard(receiptText);
                        });
                } catch (e) {
                    console.error('复制出错:', e);
                    // 回退方法
                    fallbackCopyTextToClipboard(receiptText);
                }
            } else {
                alert('没有可复制的收据内容');
            }
        });
    }
    
    // 回退复制方法
    function fallbackCopyTextToClipboard(text) {
        try {
            // 创建临时textarea
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            // 尝试执行复制命令
            const successful = document.execCommand('copy');
            if (successful) {
                alert('收据已复制到剪贴板!');
                console.log('收据复制成功(回退方法)');
            } else {
                console.error('回退复制失败');
                alert('复制失败，请手动复制');
            }
            
            // 清理
            document.body.removeChild(textArea);
        } catch (err) {
            console.error('回退复制出错:', err);
            alert('复制失败，请手动复制');
        }
    }
    
    // WhatsApp分享功能
    if (whatsappShare) {
        whatsappShare.addEventListener('click', function() {
            console.log('WhatsApp分享按钮被点击');
            let receiptText = receipt ? receipt.value : '';
            if (receiptText) {
                // 编码文本以在URL中使用
                let encodedText = encodeURIComponent(receiptText);
                window.open(`https://api.whatsapp.com/send?text=${encodedText}`, '_blank');
            }
        });
    }
    
    // 监听表单响应后页面加载完成
    window.addEventListener('load', function() {
        console.log('页面及所有资源加载完成');
        if (receipt && receipt.value) {
            console.log('收据内容存在，长度:', receipt.value.length);
            
            // 检查是否成功下注
            if (receipt.value.includes("===== 下注成功 =====")) {
                console.log('检测到成功下注');
                
                // 高亮收据区域
                receipt.classList.add('border-success');
                receipt.style.backgroundColor = '#f0fff0';
                
                // 滚动到收据部分
                receipt.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // 闪烁复制按钮提示用户
                if (copyReceipt) {
                    let flashCount = 0;
                    const flashButton = setInterval(function() {
                        copyReceipt.classList.toggle('btn-success');
                        copyReceipt.classList.toggle('btn-warning');
                        flashCount++;
                        if (flashCount > 5) {
                            clearInterval(flashButton);
                            copyReceipt.classList.remove('btn-warning');
                            copyReceipt.classList.add('btn-success');
                        }
                    }, 500);
                }
                
                // 清空下注内容，准备下一次下注
                if (betContent) {
                    betContent.value = 'D\n#';
                }
                
                // 添加新下注按钮
                const formButtons = document.querySelector('#betForm .d-flex');
                if (formButtons && !document.getElementById('newBetBtn')) {
                    const newBetBtn = document.createElement('button');
                    newBetBtn.id = 'newBetBtn';
                    newBetBtn.type = 'button';
                    newBetBtn.className = 'btn me-2';
                    newBetBtn.style.backgroundColor = '#17a2b8';
                    newBetBtn.style.color = 'white';
                    newBetBtn.style.border = 'none';
                    newBetBtn.innerHTML = '<i class="bi bi-plus-circle"></i> 新下注';
                    newBetBtn.addEventListener('click', function() {
                        // 清空收据区域
                        if (receipt) {
                            receipt.value = '准备投注...\n请输入下注内容并点击【投注】按钮。';
                            receipt.classList.remove('border-success');
                            receipt.style.backgroundColor = '';
                        }
                    });
                    formButtons.appendChild(newBetBtn);
                }
            }
        }
    });
});
</script> 

<!-- 测试 D 格式的表单 -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">测试 D 格式</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo url('order/create'); ?>" id="testDForm">
                        <input type="hidden" name="bet_content" value="D 10">
                        <input type="hidden" name="preview" value="1">
                        <button type="submit" class="btn" style="background-color: #dc3545; color: white; border: none;">
                            <i class="bi bi-calculator"></i> 测试 D 10 格式
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 