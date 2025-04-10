<?php include_once ROOT_PATH . '/views/layout/header.php'; ?>
<?php include_once ROOT_PATH . '/includes/transaction_helpers.php'; ?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-secondary rounded h-100 p-4">
                <h6 class="mb-4">佣金余额报表</h6>
                
                <form method="get" class="row mb-4">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">开始日期</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : date('Y-m-01') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">结束日期</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : date('Y-m-t') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3" style="margin-top: 32px;">
                            <button type="submit" class="btn btn-primary">筛选</button>
                        </div>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="bg-secondary rounded p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-2">总佣金余额</p>
                                <h3 class="mb-0"><?= number_format($totalCommission, 2) ?></h3>
                            </div>
                            <i class="fa fa-chart-bar fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/views/layout/footer.php'; ?> 