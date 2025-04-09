<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">下注</h1>
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
            
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">下注信息</h5>
                </div>
                <div class="card-body">
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr class="bg-light">
                                    <th class="bg-primary text-white"></th>
                                    <th class="bg-warning">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span>万能 (#1)</span>
                                            <img src="<?php echo asset('images/lottery/magnum.png'); ?>" alt="万能" width="25" class="ms-1">
                                        </div>
                                    </th>
                                    <th class="bg-primary text-white">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span>四合彩 (#2)</span>
                                            <img src="<?php echo asset('images/lottery/damacai.png'); ?>" alt="四合彩" width="25" class="ms-1">
                                        </div>
                                    </th>
                                    <th class="bg-danger text-white">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span>多多 (#3)</span>
                                            <img src="<?php echo asset('images/lottery/toto.png'); ?>" alt="多多" width="25" class="ms-1">
                                        </div>
                                    </th>
                                    <th class="bg-info text-white">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span>新加坡 (#4)</span>
                                            <img src="<?php echo asset('images/lottery/singapore.png'); ?>" alt="新加坡" width="25" class="ms-1">
                                        </div>
                                    </th>
                                    <th class="bg-danger text-white">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span>沙巴 (#5)</span>
                                            <img src="<?php echo asset('images/lottery/sabah.png'); ?>" alt="沙巴" width="25" class="ms-1">
                                        </div>
                                    </th>
                                    <th class="bg-success text-white">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span>山打根 (#6)</span>
                                            <img src="<?php echo asset('images/lottery/sandakan.png'); ?>" alt="山打根" width="25" class="ms-1">
                                        </div>
                                    </th>
                                    <th class="bg-success text-white">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span>砂拉越 (#7)</span>
                                            <img src="<?php echo asset('images/lottery/sarawak.png'); ?>" alt="砂拉越" width="25" class="ms-1">
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
                    
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="<?php echo url('order/create'); ?>" id="betForm">
                                <div class="mb-3">
                                    <label for="bet_content" class="form-label">输入</label>
                                    <textarea class="form-control" id="bet_content" name="bet_content" rows="15" placeholder="D&#10;#"><?php echo isset($bet_content) ? h($bet_content) : 'D
#'; ?></textarea>
                                </div>
                                
                                <div class="d-flex">
                                    <button type="button" class="btn me-2" id="submitBet" style="background-color: #dc3545; color: white; border: none;">
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
                                if (isset($parsed_result) && $parsed_result['valid'] && isset($_POST['confirm'])) {
                                    // 获取单号（使用当前日期+随机数创建）
                                    $order_id = date('ymd') . rand(10, 99) . '(' . rand(1, 9) . ')';
                                    
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
                                    $lottery_str = count($lottery_types) > 0 ? '*' . implode('', $lottery_types) : '*MPTS';
                                    
                                    // 购买的号码和下注种类
                                    $bet_details = [];
                                    foreach ($parsed_result['items'] as $item) {
                                        if (isset($item['number']) && isset($item['bet_type'])) {
                                            $bet_details[] = $item['number'] . '= ' . $item['bet_type'];
                                        }
                                    }
                                    $bet_str = implode("\n", $bet_details);
                                    if (empty($bet_str)) {
                                        $bet_str = "1234= B1 S1";
                                    }
                                    
                                    // 输出收据
                                    echo "{$order_id} > 单号\n";
                                    echo "{$username} > 用户名\n";
                                    echo "{$date} > 期（日期）\n";
                                    echo "{$lottery_str} > （开彩种类）\n";
                                    echo "{$bet_str}\n";
                                    echo "GT={$parsed_result['total']} > 总数";
                                } else if (isset($parsed_result) && $parsed_result['valid']) {
                                    // 预览模式 - 简化格式
                                    echo "===== 下注预览 =====\n";
                                    echo "日期: " . date('Y-m-d H:i:s') . "\n";
                                    echo "用户: " . $_SESSION['user']['username'] . "\n";
                                    echo "========================================\n";
                                    
                                    foreach ($parsed_result['items'] as $item) {
                                        echo $item['type'] . " - " . $item['description'] . " - " . $item['amount'] . "\n";
                                    }
                                    
                                    echo "========================================\n";
                                    echo "总金额: " . $parsed_result['total'] . "\n";
                                    echo "\n点击【确认下注】完成下注";
                                }
                                ?></textarea>
                            </div>
                            
                            <?php if (isset($parsed_result) && $parsed_result['valid']): ?>
                            <div class="d-flex">
                                <button type="button" class="btn me-2" id="inputToNote" style="background-color: #198754; color: white; border: none;">
                                    <i class="bi bi-clipboard"></i> 输入下注
                                </button>
                                <button type="button" class="btn" id="whatsappShare" style="background-color: #0d6efd; color: white; border: none;">
                                    <i class="bi bi-whatsapp"></i> 发送到WhatsApp
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="d-flex">
                                <button type="button" class="btn me-2" id="inputToNote" style="background-color: #198754; color: white; border: none;" disabled>
                                    <i class="bi bi-clipboard"></i> 输入下注
                                </button>
                                <button type="button" class="btn" id="whatsappShare" style="background-color: #0d6efd; color: white; border: none;" disabled>
                                    <i class="bi bi-whatsapp"></i> 发送到WhatsApp
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">下注说明</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered">
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
                                <table class="table table-bordered">
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

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 投注按钮功能 - 确保提交表单
    const betForm = document.getElementById('betForm');
    const submitBetBtn = document.getElementById('submitBet');
    
    submitBetBtn.addEventListener('click', function() {
        // 验证输入不为空
        const betContent = document.getElementById('bet_content').value.trim();
        if (!betContent) {
            alert('下注内容不能为空');
            return;
        }
        
        // 添加一个隐藏字段表示这是预览操作
        let previewInput = betForm.querySelector('input[name="preview"]');
        if (!previewInput) {
            previewInput = document.createElement('input');
            previewInput.type = 'hidden';
            previewInput.name = 'preview';
            previewInput.value = '1';
            betForm.appendChild(previewInput);
        }
        
        // 提交表单
        betForm.submit();
    });
    
    // 清除按钮功能
    document.getElementById('clearButton').addEventListener('click', function() {
        document.getElementById('bet_content').value = 'D\n#';
        document.getElementById('receipt').value = '';
    });
    
    // 输入下注按钮 - 提交确认
    if (document.getElementById('inputToNote')) {
        document.getElementById('inputToNote').addEventListener('click', function() {
            // 移除preview输入（如果存在）
            const previewInput = betForm.querySelector('input[name="preview"]');
            if (previewInput) {
                betForm.removeChild(previewInput);
            }
            
            // 创建一个隐藏字段表示这是确认操作
            let confirmInput = betForm.querySelector('input[name="confirm"]');
            if (!confirmInput) {
                confirmInput = document.createElement('input');
                confirmInput.type = 'hidden';
                confirmInput.name = 'confirm';
                confirmInput.value = '1';
                betForm.appendChild(confirmInput);
            }
            
            // 提交表单
            betForm.submit();
        });
    }
    
    // 初始化 - 确保初始加载时收据区域为空（如果不是POST请求）
    if (!window.location.search.includes('preview') && !window.location.search.includes('confirm')) {
        document.getElementById('receipt').value = '';
    }
    
    // WhatsApp分享功能
    if (document.getElementById('whatsappShare')) {
        document.getElementById('whatsappShare').addEventListener('click', function() {
            let receiptText = document.getElementById('receipt').value;
            if (receiptText) {
                // 编码文本以在URL中使用
                let encodedText = encodeURIComponent(receiptText);
                window.open(`https://api.whatsapp.com/send?text=${encodedText}`, '_blank');
            }
        });
    }
});
</script> 