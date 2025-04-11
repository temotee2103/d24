<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - D24 Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            --secondary-gradient: linear-gradient(135deg, #10B981 0%, #3B82F6 100%);
            --accent-gradient: linear-gradient(135deg, #F43F5E 0%, #EC4899 100%);
            --card-border-radius: 16px;
            --button-border-radius: 12px;
            --body-bg: #F5F7FA;
            --card-bg: rgba(255, 255, 255, 0.85);
        }
        
        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--body-bg);
            color: #1E293B;
        }
        
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 50%, rgba(255, 255, 255, 0) 100%);
            z-index: -1;
            animation: pulse 15s infinite ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 0 20px;
            margin: auto;
        }
        
        .modern-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--card-border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05), 
                        0 1px 4px rgba(0, 0, 0, 0.1), 
                        0 0 0 1px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            transition: all 0.3s ease;
            padding: 0;
        }
        
        .modern-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 
                        0 2px 8px rgba(0, 0, 0, 0.12), 
                        0 0 0 1px rgba(0, 0, 0, 0.02);
            transform: translateY(-5px);
        }
        
        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--card-bg);
            border-radius: 50% 50% 0 0;
        }
        
        .logo {
            max-width: 120px;
            margin-bottom: 1rem;
            filter: brightness(0) invert(1);
            position: relative;
            z-index: 2;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
            border-radius: var(--button-border-radius);
            padding: 12px 20px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
            background: linear-gradient(135deg, #5254D0 0%, #7B4FDE 100%);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(99, 102, 241, 0.3);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #6366F1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .form-label {
            font-weight: 500;
            color: #1E293B;
            margin-bottom: 8px;
        }
        
        .text-muted {
            color: #64748B !important;
        }
        
        .alert-danger {
            background: rgba(244, 63, 94, 0.1);
            border: none;
            color: #F43F5E;
            border-radius: 10px;
        }
        
        /* Mobile Adjustments */
        @media (max-width: 576px) {
            body {
                padding: 15px 0; /* Even less body padding */
            }
            .login-container {
                padding: 0 10px; /* Less container padding */
            }
            .card-header {
                padding: 15px 15px 25px 15px; /* Reduced padding, more bottom for curve */
            }
            .card-body {
                padding: 25px 15px; /* Adjust body padding */
            }
            .logo {
                max-width: 80px; /* Smaller logo */
                margin-bottom: 0.5rem; /* Less margin */
            }
            .card-header h1 {
                font-size: 1rem; /* Smaller title */
            }
            /* Adjust the curve */
            .card-header::after {
                height: 40px; /* Less tall curve */
                bottom: -20px; /* Adjust position */
            }

            .card-body h4 {
                 font-size: 1.2rem; 
                 margin-bottom: 1rem !important;
            }
            .form-control {
                padding: 10px 12px; /* Adjust padding */
                font-size: 0.9rem; /* Slightly smaller font */
            }
            .btn-lg {
                padding: 10px 16px;
                font-size: 0.95rem; /* Slightly smaller button font */
            }
            .alert {
                padding: 0.7rem;
                font-size: 0.85rem;
            }
            .form-label {
                margin-bottom: 5px; /* Less space below label */
                font-size: 0.9rem;
            }
            .mb-4 {
                 margin-bottom: 1rem !important; /* Reduce spacing between fields */
            }
            .mt-5 {
                 margin-top: 1.5rem !important; /* Reduce space above button */
            }
            .mt-4 {
                margin-top: 1rem !important; /* Reduce space above copyright */
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="modern-card">
            <div class="card-header">
                <img class="logo" src="<?php echo asset('images/logo.png'); ?>" alt="D24 Logo">
            </div>
            
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">欢迎登录</h4>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo url('auth/login'); ?>">
                    <div class="mb-4">
                        <label for="username" class="form-label">
                            <i class="bi bi-person me-2"></i>用户名
                        </label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="请输入用户名" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>密码
                        </label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码" required>
                    </div>
                    
                    <div class="d-grid gap-2 mt-5">
                        <button class="btn btn-primary btn-lg" type="submit">
                            登录 <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
                
                <p class="mt-4 mb-0 text-center text-muted small">
                    &copy; <?php echo date('Y'); ?> D24 Dashboard - 保留所有权利
                </p>
            </div>
        </div>
    </div>
</body>
</html> 