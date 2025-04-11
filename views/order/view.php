<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">订单详情</h1>
        <div>
            <?php if ($order['status'] === 'pending'): ?>
                <a href="<?php echo url('order/cancel/' . $order['id']); ?>" class="btn btn-danger" onclick="return confirm('确定要取消该订单吗？');">
                    <i class="bi bi-x-lg"></i> 取消订单
                </a>
            <?php endif; ?>
            <a href="<?php echo url('order'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> 返回订单列表
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">订单信息</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th width="30%">订单号</th>
                            <td><?php echo $order['order_number']; ?></td>
                        </tr>
                        <tr>
                            <th>用户</th>
                            <td><?php echo h($order['username']); ?></td>
                        </tr>
                        <tr>
                            <th>总金额</th>
                            <td><?php echo $order['total_amount']; ?></td>
                        </tr>
                        <tr>
                            <th>状态</th>
                            <td>
                                <?php 
                                    switch ($order['status']) {
                                        case 'completed':
                                            echo '<span class="badge bg-success">已完成</span>';
                                            break;
                                        case 'pending':
                                            echo '<span class="badge bg-warning">处理中</span>';
                                            break;
                                        case 'cancelled':
                                            echo '<span class="badge bg-danger">已取消</span>';
                                            break;
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>创建时间</th>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>最后更新</th>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($order['updated_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">订单内容</h5>
                </div>
                <div class="card-body">
                                    <?php 
                    // Check if parsed_content exists and is valid
                    if (isset($parsed_content) && $parsed_content['valid'] && !empty($parsed_content['items'])):
                        // Generate receipt-like format
                        // Use letter codes for display
                        $display_lottery_types = $parsed_content['lottery_types_letters'] ?? [];
                        if (empty($display_lottery_types)) { 
                             $display_lottery_types = ['M','P','T','S']; // Default
                        }
                        $lottery_str = '*' . implode('', $display_lottery_types);

                        $grouped_bets = [];
                        foreach ($parsed_content['items'] as $item) {
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
                        $bet_details = [];
                        foreach ($grouped_bets as $number => $details) {
                            $bet_details[] = $number . '= ' . implode(' ', $details);
                        }
                        $bet_str = implode("\n", $bet_details);
                        $total = $parsed_content['total'] ?? $order['total_amount']; // Use parsed total if available

                        // Display as preformatted text
                        echo "<pre class=\"border p-3 bg-light\" style=\"white-space: pre-wrap; word-wrap: break-word;\">";
                        echo h($order['order_number']) . "\n";
                        echo h($order['username']) . "\n";
                        echo date('d/m', strtotime($order['created_at'])) . "\n"; // Use order creation date
                        echo h($lottery_str) . "\n";
                        echo h($bet_str) . "\n";
                        echo "\n";
                        echo "GT=" . h($total);
                        echo "</pre>";

                    else: 
                        // Fallback if parsing fails or no items
                    ?>
                        <div class="alert alert-info">订单内容无法解析或为空</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 