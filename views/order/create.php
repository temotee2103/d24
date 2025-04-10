<!DOCTYPE html>
<html>
<head>
    <title>创建订单</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'views/partials/header.php'; ?>
    
    <div class="container">
        <h1>创建订单</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>当前期数信息</h2>
            </div>
            <div class="card-body">
                <?php if (isset($currentPeriod) && $currentPeriod): ?>
                    <p><strong>期数:</strong> <?php echo $currentPeriod['period_number']; ?></p>
                    <p><strong>开始时间:</strong> <?php echo $currentPeriod['start_time']; ?></p>
                    <p><strong>结束时间:</strong> <?php echo $currentPeriod['end_time']; ?></p>
                    <p><strong>状态:</strong> <?php echo $currentPeriod['status']; ?></p>
                <?php else: ?>
                    <p class="text-danger">当前没有开放的期数</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isset($currentPeriod) && $currentPeriod): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h2>下注内容</h2>
                </div>
                <div class="card-body">
                    <form action="?controller=order&action=create" method="post">
                        <div class="form-group">
                            <label for="bet_content">输入下注内容:</label>
                            <textarea id="bet_content" name="bet_content" class="form-control" rows="5" required></textarea>
                            <small class="text-muted">格式: 期数/彩种/玩法/号码/金额, 例如: 20230101/SSQ/直选/123/100</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">提交下注</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'views/partials/footer.php'; ?>
</body>
</html> 