<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">下注</h1>
    </div>
    
    <!-- Opening Dates Section -->
    <div class="card mb-4 modern-card opening-dates-section">
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
    <div class="card mb-4 modern-card user-info-section">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-borderless modern-table mb-0" style="color: #333;">
                    <thead style="background-color: #ffb74d;">
                        <tr>
                            <th>Account</th>
                            <th>Balance (RM)</th>
                            <th>Bet Format</th>
                            <th>Box / iBox</th>
                            <th>Draw Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo h($user['username']); ?></td>
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
            
            <div class="card mb-4 modern-card betting-area">
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-md-6 input-column">
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
                        
                        <div class="col-md-6 receipt-column">
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
                                        
                                        // 获取彩种类型 (Use Letters)
                                        $display_lottery_types = $parsed_result['lottery_types_letters'] ?? [];
                                        // Fallback or default if empty (shouldn't happen with current parser logic)
                                        if (empty($display_lottery_types)) { 
                                             $display_lottery_types = ['M','P','T','S']; // Default to MPTS letters 
                                        }
                                        $lottery_str = '*' . implode('', $display_lottery_types);
                                        
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
            
            <div class="col-md-12 mt-4 betting-instructions-section">
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

<!-- Success Modal -->
<!-- Error Modal -->
<!-- Modals moved to footer.php -->

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('页面加载完成 - order/create');
    
    // This PHP block triggers the modal based on controller data
    <?php if (isset($popup_message) && $popup_message): ?>
        const messageType = "<?php echo $popup_message['type']; ?>";
        const messageText = "<?php echo addslashes($popup_message['text']); ?>";
        const messageTitle = "<?php echo isset($popup_message['title']) ? addslashes($popup_message['title']) : null; ?>";
        
        if (messageType === 'success') {
            // Get the receipt content from the (now hidden) textarea
            const receiptTextarea = document.getElementById('receipt');
            const receiptContent = receiptTextarea ? receiptTextarea.value : null;
            // Show success modal with receipt content
            window.showSuccessModal(messageText, messageTitle || "下注成功！", receiptContent);
        } else { 
            window.showErrorModal(messageText, messageTitle || "操作失败");
        }
    <?php endif; ?>

    // --- Page-Specific JS (Betting form, buttons etc.) ---
    const betForm = document.getElementById('betForm');
    // ... rest of the existing page-specific JS ...
    
});
</script> 