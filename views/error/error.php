<?php 
// 确保有错误代码和消息
if(!isset($code)) $code = 500;
if(!isset($message)) $message = '服务器内部错误';

// 尝试包含头部文件
try {
    include_once ROOT_PATH . '/views/layout/header.php';
} catch (Exception $e) {
    // 如果头部文件无法加载，提供基本的HTML结构
    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系统错误 - ' . (isset($GLOBALS['config']['app_name']) ? $GLOBALS['config']['app_name'] : 'D24 Dashboard') . '</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .error-logo {
                max-width: 150px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
    <div class="container text-center py-5">
        <img src="' . asset('images/logo.png') . '" alt="D24 Logo" class="error-logo">
    ';
}
?>

<div class="container-fluid py-4">
    <div class="alert alert-danger text-center">
        <h1>错误 <?php echo $code; ?></h1>
        <p><?php echo $message; ?></p>
        <p>
            <a href="<?php echo isset($GLOBALS['config']['base_url']) ? $GLOBALS['config']['base_url'] : '/'; ?>" class="btn btn-primary">返回首页</a>
        </p>
    </div>
</div>

<?php 
// 尝试包含底部文件
try {
    include_once ROOT_PATH . '/views/layout/footer.php';
} catch (Exception $e) {
    // 如果底部文件无法加载，关闭HTML标签
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
}
?> 